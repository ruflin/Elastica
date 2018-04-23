<?php
namespace Elastica\Test\Processor;

use Elastica\Bulk;
use Elastica\Document;
use Elastica\Processor\Convert;
use Elastica\ResultSet;
use Elastica\Test\BasePipeline as BasePipelineTest;

class ConvertTest extends BasePipelineTest
{
    /**
     * @group unit
     */
    public function testConvert()
    {
        $processor = new Convert('foo', 'integer');

        $expected = [
            'convert' => [
                'field' => 'foo',
                'type' => 'integer',
            ],
        ];

        $this->assertEquals($expected, $processor->toArray());
    }

    /**
     * @group unit
     */
    public function testConvertWithNonDefaultOptions()
    {
        $processor = new Convert('foo', 'integer');
        $processor->setIgnoreMissing(true);

        $expected = [
            'convert' => [
                'field' => 'foo',
                'type' => 'integer',
                'ignore_missing' => true,
            ],
        ];

        $this->assertEquals($expected, $processor->toArray());

        $processor->setTargetField('field2');

        $expected = [
            'convert' => [
                'field' => 'foo',
                'type' => 'integer',
                'ignore_missing' => true,
                'target_field' => 'field2',
            ],
        ];

        $this->assertEquals($expected, $processor->toArray());
    }

    /**
     * @group functional
     */
    public function testConvertField()
    {
        $append = new Convert('foo', 'float');

        $pipeline = $this->_createPipeline('my_custom_pipeline', 'pipeline for Convert');
        $pipeline->addProcessor($append)->create();

        $index = $this->_createIndex();
        $type = $index->getType('bulk_test');

        // Add document to normal index
        $doc1 = new Document(null, ['name' => 'ruflin', 'type' => 'elastica', 'foo' => '5.290']);
        $doc2 = new Document(null, ['name' => 'nicolas', 'type' => 'elastica', 'foo' => '6.908']);

        $bulk = new Bulk($index->getClient());
        $bulk->setIndex($index);
        $bulk->setType($type);

        $bulk->addDocuments([
            $doc1, $doc2,
        ]);
        $bulk->setRequestParam('pipeline', 'my_custom_pipeline');

        $bulk->send();
        $index->refresh();

        /** @var ResultSet $result */
        $result = $index->search('elastica');

        $this->assertCount(2, $result->getResults());

        $results = $result->getResults();
        foreach ($results as $result) {
            $value = $result->getData();
            $this->assertInternalType('float', $value['foo']);
        }

        $this->assertSame(5.290, ($results[0]->getHit())['_source']['foo']);
        $this->assertSame(6.908, ($results[1]->getHit())['_source']['foo']);
    }
}
