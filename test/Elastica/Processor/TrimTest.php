<?php

namespace Elastica\Test\Processor;

use Elastica\Bulk;
use Elastica\Document;
use Elastica\Processor\Trim;
use Elastica\ResultSet;
use Elastica\Test\BasePipeline as BasePipelineTest;

class TrimTest extends BasePipelineTest
{
    /**
     * @group unit
     */
    public function testTrim()
    {
        $processor = new Trim('foo');

        $expected = [
            'trim' => [
                'field' => 'foo',
            ],
        ];

        $this->assertEquals($expected, $processor->toArray());
    }

    /**
     * @group functional
     */
    public function testTrimField()
    {
        $trim = new Trim('name');

        $pipeline = $this->_createPipeline('my_custom_pipeline', 'pipeline for Trim');
        $pipeline->addProcessor($trim)->create();

        $index = $this->_createIndex();
        $type = $index->getType('_doc');

        // Add document to normal index
        $doc1 = new Document(null, ['name' => '   ruflin   ']);
        $doc2 = new Document(null, ['name' => '     nicolas     ']);

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
        $result = $index->search('*');

        $this->assertCount(2, $result->getResults());

        $results = $result->getResults();
        $this->assertSame('ruflin', ($results[0]->getHit())['_source']['name']);
        $this->assertSame('nicolas', ($results[1]->getHit())['_source']['name']);
    }
}
