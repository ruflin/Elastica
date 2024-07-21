<?php

declare(strict_types=1);

namespace Elastica\Test\Processor;

use Elastica\Bulk;
use Elastica\Document;
use Elastica\Processor\JoinProcessor;
use Elastica\Test\BasePipeline as BasePipelineTest;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class JoinProcessorTest extends BasePipelineTest
{
    #[Group('unit')]
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

    #[Group('unit')]
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

    #[Group('functional')]
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
