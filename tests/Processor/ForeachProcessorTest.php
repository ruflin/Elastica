<?php

declare(strict_types=1);

namespace Elastica\Test\Processor;

use Elastica\Bulk;
use Elastica\Document;
use Elastica\Processor\ForeachProcessor;
use Elastica\Processor\UppercaseProcessor;
use Elastica\Test\BasePipeline as BasePipelineTest;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class ForeachProcessorTest extends BasePipelineTest
{
    #[Group('unit')]
    public function testForeachProcessorDefault(): void
    {
        $subprocessor = new UppercaseProcessor('field2');
        $processor = new ForeachProcessor('field1', $subprocessor);

        $expected = [
            'foreach' => [
                'field' => 'field1',
                'processor' => [
                    'uppercase' => [
                        'field' => 'field2',
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $processor->toArray());
    }

    #[Group('unit')]
    public function testForeachRawProcessorDefault(): void
    {
        $subprocessor = [
            'uppercase' => [
                'field' => 'field2',
            ],
        ];
        $processor = new ForeachProcessor('field1', $subprocessor);

        $expected = [
            'foreach' => [
                'field' => 'field1',
                'processor' => [
                    'uppercase' => [
                        'field' => 'field2',
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $processor->toArray());
    }

    #[Group('unit')]
    public function testForeachProcessorIgnoreMissing(): void
    {
        $subprocessor = new UppercaseProcessor('field2');
        $processor = new ForeachProcessor('field1', $subprocessor);
        $processor->setIgnoreMissing(true);

        $expected = [
            'foreach' => [
                'field' => 'field1',
                'processor' => [
                    'uppercase' => [
                        'field' => 'field2',
                    ],
                ],
                'ignore_missing' => true,
            ],
        ];

        $this->assertEquals($expected, $processor->toArray());
    }

    #[Group('functional')]
    public function testForeachProcessor(): void
    {
        $subprocessor = new UppercaseProcessor('_ingest._value');
        $foreach = new ForeachProcessor('values', $subprocessor);

        $pipeline = $this->_createPipeline('my_custom_pipeline', 'pipeline for Foreach');
        $pipeline->addProcessor($foreach)->create();

        $index = $this->_createIndex();
        $bulk = new Bulk($index->getClient());
        $bulk->setIndex($index);

        $bulk->addDocuments([
            new Document(null, ['name' => 'ruflin', 'type' => 'elastica', 'values' => ['foo', 'bar', 'baz']]),
        ]);
        $bulk->setRequestParam('pipeline', 'my_custom_pipeline');

        $bulk->send();
        $index->refresh();

        $result = $index->search('*');

        $this->assertCount(1, $result->getResults());

        foreach ($result->getResults() as $rx) {
            $value = $rx->getData();
            $this->assertCount(3, $value['values']);
            $this->assertEquals('FOO', $value['values'][0]);
            $this->assertEquals('BAR', $value['values'][1]);
            $this->assertEquals('BAZ', $value['values'][2]);
        }
    }
}
