<?php
namespace Elastica\Test\Processor;

use Elastica\Bulk;
use Elastica\Document;
use Elastica\Processor\DotExpander;
use Elastica\ResultSet;
use Elastica\Test\BasePipeline as BasePipelineTest;

class DotExpanderTest extends BasePipelineTest
{
    /**
     * @group unit
     */
    public function testDotExpander()
    {
        $processor = new DotExpander('foo.bar');

        $expected = [
            'dot_expander' => [
                'field' => 'foo.bar',
            ],
        ];

        $this->assertEquals($expected, $processor->toArray());
    }

    /**
     * @group functional
     */
    public function testDotExpanderField()
    {
        $dotExpander = new DotExpander('foo.bar');

        $pipeline = $this->_createPipeline('my_custom_pipeline', 'pipeline for DotExpander');
        $pipeline->addProcessor($dotExpander)->create();

        $index = $this->_createIndex();
        $type = $index->getType('bulk_test');

        // Add document to normal index
        $doc1 = new Document(null, ['foo.bar' => 'value']);

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

        $expect = [
            'foo' => [
                'bar' => 'value',
            ],
        ];
        $results = $result->getResults();
        $this->assertEquals($expect, ($results[0]->getHit())['_source']);
    }
}
