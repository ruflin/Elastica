<?php
namespace Elastica\Test\Processor;

use Elastica\Bulk;
use Elastica\Document;
use Elastica\Processor\Sort;
use Elastica\ResultSet;
use Elastica\Test\BasePipeline as BasePipelineTest;

class SortTest extends BasePipelineTest
{
    /**
     * @group unit
     */
    public function testSort()
    {
        $processor = new Sort('field_to_sort');

        $expected = [
            'sort' => [
                'field' => 'field_to_sort',
            ],
        ];

        $this->assertEquals($expected, $processor->toArray());
    }

    /**
     * @group unit
     */
    public function testSortWithNonDefaultOptions()
    {
        $processor = new Sort('field_to_sort');
        $processor->setOrder('desc');

        $expected = [
            'sort' => [
                'field' => 'field_to_sort',
                'order' => 'desc',
            ],
        ];

        $this->assertEquals($expected, $processor->toArray());
    }

    /**
     * @group functional
     */
    public function testSortField()
    {
        $sort = new Sort('name');

        $pipeline = $this->_createPipeline('my_custom_pipeline', 'pipeline for Sort');
        $pipeline->addProcessor($sort)->create();

        $index = $this->_createIndex();
        $type = $index->getType('bulk_test');

        // Add document to normal index
        $doc1 = new Document(null, ['name' => [10, 9, 8, 7, 6, 5, 4, 3, 2, 1]]);

        $bulk = new Bulk($index->getClient());
        $bulk->setIndex($index);
        $bulk->setType($type);

        $bulk->addDocument($doc1);
        $bulk->setRequestParam('pipeline', 'my_custom_pipeline');

        $bulk->send();
        $index->refresh();

        /** @var ResultSet $result */
        $result = $index->search('*');

        $this->assertCount(1, $result->getResults());

        $results = $result->getResults();
        $this->assertSame([1, 2, 3, 4, 5, 6, 7, 8, 9, 10], ($results[0]->getHit())['_source']['name']);
    }
}
