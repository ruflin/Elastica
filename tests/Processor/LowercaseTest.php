<?php

namespace Elastica\Test\Processor;

use Elastica\Bulk;
use Elastica\Document;
use Elastica\Processor\Lowercase;
use Elastica\ResultSet;
use Elastica\Test\BasePipeline as BasePipelineTest;

/**
 * @internal
 */
class LowercaseTest extends BasePipelineTest
{
    /**
     * @group unit
     */
    public function testLowercase(): void
    {
        $processor = new Lowercase('foo');

        $expected = [
            'lowercase' => [
                'field' => 'foo',
            ],
        ];

        $this->assertEquals($expected, $processor->toArray());
    }

    /**
     * @group functional
     */
    public function testLowercaseField(): void
    {
        $lcase = new Lowercase('name');

        $pipeline = $this->_createPipeline('my_custom_pipeline', 'pipeline for Lowercase');
        $pipeline->addProcessor($lcase)->create();

        $index = $this->_createIndex();
        $bulk = new Bulk($index->getClient());
        $bulk->setIndex($index);

        $bulk->addDocuments([
            new Document(null, ['name' => 'RUFLIN']),
            new Document(null, ['name' => 'NICOLAS']),
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
