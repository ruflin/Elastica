<?php

namespace Elastica\Test\Processor;

use Elastica\Processor\BytesProcessor;
use Elastica\Test\BasePipeline as BasePipelineTest;

/**
 * @internal
 */
class BytesProcessorTest extends BasePipelineTest
{
    /**
     * @group unit
     */
    public function testBytes(): void
    {
        $processor = new BytesProcessor('foo');

        $expected = [
            'bytes' => [
                'field' => 'foo',
            ],
        ];

        $this->assertEquals($expected, $processor->toArray());
    }

    /**
     * @group unit
     */
    public function testBytesWithTargetField(): void
    {
        $processor = (new BytesProcessor('foo'))
            ->setTargetField('bar')
        ;

        $expected = [
            'bytes' => [
                'field' => 'foo',
                'target_field' => 'bar',
            ],
        ];

        $this->assertEquals($expected, $processor->toArray());
    }

    /**
     * @group unit
     */
    public function testBytesWithNonDefaultOptions(): void
    {
        $processor = (new BytesProcessor('foo'))
            ->setIgnoreFailure(true)
            ->setIgnoreMissing(true)
        ;

        $expected = [
            'bytes' => [
                'field' => 'foo',
                'ignore_failure' => true,
                'ignore_missing' => true,
            ],
        ];

        $this->assertEquals($expected, $processor->toArray());
    }
}
