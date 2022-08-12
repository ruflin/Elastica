<?php

namespace Elastica\Test\Processor;

use Elastica\Bulk;
use Elastica\Document;
use Elastica\Processor\JoinProcessor;
use Elastica\Test\BasePipeline as BasePipelineTest;

/**
 * @internal
 */
class JoinProcessorTest extends BasePipelineTest
{
    /**
     * @group unit
     */
    public function testJoin(): void
    {
        $processor = new JoinProcessor('joined_array_field', '-');

        $expected = [
            'join' => [
                'field' => 'joined_array_field',
                'separator' => '-',
            ],
        ];

        $this->assertEquals($expected, $processor->toArray());
    }

    /**
     * @group unit
     */
    public function testJoinWithNonDefaultOptions(): void
    {
        $processor = (new JoinProcessor('joined_array_field', '-'))
            ->setIgnoreFailure(true)
        ;

        $expected = [
            'join' => [
                'field' => 'joined_array_field',
                'separator' => '-',
                'ignore_failure' => true,
            ],
        ];

        $this->assertEquals($expected, $processor->toArray());
    }

    /**
     * @group functional
     */
    public function testJoinField(): void
    {
        $join = new JoinProcessor('name', '-');

        $pipeline = $this->_createPipeline('my_custom_pipeline', 'pipeline for Join');
        $pipeline->addProcessor($join)->create();

        $index = $this->_createIndex();
        $bulk = new Bulk($index->getClient());
        $bulk->setIndex($index);

        $bulk->addDocument(new Document(null, ['name' => ['abc', 'def', 'ghij']]));
        $bulk->setRequestParam('pipeline', 'my_custom_pipeline');

        $bulk->send();
        $index->refresh();

        $result = $index->search('*');

        $this->assertCount(1, $result->getResults());

        $results = $result->getResults();
        $this->assertSame('abc-def-ghij', $results[0]->getHit()['_source']['name']);
    }
}
