<?php

declare(strict_types=1);

namespace Elastica\Test\Processor;

use Elastica\Processor\BytesProcessor;
use Elastica\Test\BasePipeline as BasePipelineTest;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class BytesProcessorTest extends BasePipelineTest
{
    #[Group('unit')]
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

    #[Group('unit')]
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

    #[Group('unit')]
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
