<?php

namespace Elastica\Test\Processor;

use Elastica\Processor\AbstractProcessor;
use Elastica\Processor\Set;
use Elastica\Test\BasePipeline;

/**
 * @internal
 */
abstract class BaseProcessorTestCase extends BasePipeline
{
    /**
     * @dataProvider validProcessorProvider
     * @group unit
     */
    public function testBaseFields(AbstractProcessor $processor): void
    {
        $onFailureProcessor = new Set('error', "there's a problem");

        $processor
            ->setTag('mytag')
            ->setOnFailure($onFailureProcessor)
            ->setIgnoreFailure(true)
            ->setIf("ctx.my == 'if'")
        ;

        $asArray = $processor->toArray();
        $body = \reset($asArray);

        $this->assertArrayHasKey('if', $body);
        $this->assertEquals("ctx.my == 'if'", $body['if']);

        $this->assertArrayHasKey('on_failure', $body);
        $this->assertEquals($onFailureProcessor->toArray(), $body['on_failure']);

        $this->assertArrayHasKey('ignore_failure', $body);
        $this->assertEquals('true', $body['ignore_failure']);

        $this->assertArrayHasKey('if', $body);
        $this->assertEquals("ctx.my == 'if'", $body['if']);
    }

    abstract public function validProcessorProvider(): array;
}
