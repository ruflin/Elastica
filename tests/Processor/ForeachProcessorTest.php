<?php

namespace Elastica\Test\Processor;

use Elastica\Bulk;
use Elastica\Document;
use Elastica\Processor\ForeachProcessor;
use Elastica\Processor\Uppercase;
use Elastica\ResultSet;
use Elastica\Test\BasePipeline as BasePipelineTest;

/**
 * @internal
 */
class ForeachProcessorTest extends BasePipelineTest
{
    /**
     * @group unit
     */
    public function testForeachProcessorDefault(): void
    {
        $processor = new ForeachProcessor();
        $processor->setField('field1');

        $subprocessor = new Uppercase('field2');
        $processor->setProcessor($subprocessor);

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

    /**
     * @group unit
     */
    public function testForeachRawProcessorDefault(): void
    {
        $processor = new ForeachProcessor();
        $processor->setField('field1');

        $subprocessor = [
            'uppercase' => [
                'field' => 'field2',
            ],
        ];
        $processor->setRawProcessor($subprocessor);

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

    /**
     * @group unit
     */
    public function testForeachProcessorIgnoreMissing(): void
    {
        $processor = new ForeachProcessor();
        $processor->setField('field1');

        $subprocessor = new Uppercase('field2');
        $processor->setProcessor($subprocessor);
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

    /**
     * @group functional
     */
    public function testForeachProcessor(): void
    {
        $foreach = new ForeachProcessor();
        $foreach->setField('values');

        $subprocessor = new Uppercase('_ingest._value');
        $foreach->setProcessor($subprocessor);

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

        /** @var ResultSet $result */
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
