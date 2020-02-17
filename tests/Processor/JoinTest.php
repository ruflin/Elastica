<?php

namespace Elastica\Test\Processor;

use Elastica\Bulk;
use Elastica\Document;
use Elastica\Processor\Join;
use Elastica\ResultSet;
use Elastica\Test\BasePipeline as BasePipelineTest;

/**
 * @internal
 */
class JoinTest extends BasePipelineTest
{
    /**
     * @group unit
     */
    public function testJoin(): void
    {
        $processor = new Join('joined_array_field', '-');

        $expected = [
            'join' => [
                'field' => 'joined_array_field',
                'separator' => '-',
            ],
        ];

        $this->assertEquals($expected, $processor->toArray());
    }

    /**
     * @group functional
     */
    public function testJoinField(): void
    {
        $join = new Join('name', '-');

        $pipeline = $this->_createPipeline('my_custom_pipeline', 'pipeline for Join');
        $pipeline->addProcessor($join)->create();

        $index = $this->_createIndex();
        $bulk = new Bulk($index->getClient());
        $bulk->setIndex($index);

        $bulk->addDocument(new Document(null, ['name' => ['abc', 'def', 'ghij']]));
        $bulk->setRequestParam('pipeline', 'my_custom_pipeline');

        $bulk->send();
        $index->refresh();

        /** @var ResultSet $result */
        $result = $index->search('*');

        $this->assertCount(1, $result->getResults());

        $results = $result->getResults();
        $this->assertSame('abc-def-ghij', ($results[0]->getHit())['_source']['name']);
    }
}
