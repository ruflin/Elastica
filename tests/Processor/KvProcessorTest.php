<?php

declare(strict_types=1);

namespace Elastica\Test\Processor;

use Elastica\Processor\KvProcessor;
use Elastica\Test\BasePipeline as BasePipelineTest;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class KvProcessorTest extends BasePipelineTest
{
    #[Group('unit')]
    public function testKv(): void
    {
        $processor = new KvProcessor('message', ' ', '=');

        $expected = [
            'kv' => [
                'field' => 'message',
                'field_split' => ' ',
                'value_split' => '=',
            ],
        ];

        $this->assertEquals($expected, $processor->toArray());
    }

    #[Group('unit')]
    public function testKvWithNonDefaultOptions(): void
    {
        $processor = (new KvProcessor('message', ' ', '='))
            ->setTargetField('target_field')
            ->setIncludeKeys(['l1', 'l2'])
            ->setExcludeKeys(['l4', 'l5'])
            ->setIgnoreFailure(true)
            ->setIgnoreMissing(true)
        ;

        $expected = [
            'kv' => [
                'field' => 'message',
                'field_split' => ' ',
                'value_split' => '=',
                'target_field' => 'target_field',
                'include_keys' => ['l1', 'l2'],
                'exclude_keys' => ['l4', 'l5'],
                'ignore_failure' => true,
                'ignore_missing' => true,
            ],
        ];

        $this->assertEquals($expected, $processor->toArray());
    }

    #[Group('functional')]
    public function testKVIncludeExludeKeys(): void
    {
        $kv = new KvProcessor('field1', '&', '=');
        $kv->setExcludeKeys(['second']);

        $pipeline = $this->_createPipeline('my_custom_pipeline', 'pipeline for KV');
        $pipeline->addProcessor($kv);

        $result = $pipeline->create();

        $this->assertArrayHasKey('acknowledged', $result->getData());
        $this->assertTrue($result->getData()['acknowledged']);

        $pipelineGet = $pipeline->getPipeline('my_custom_pipeline');
        $result = $pipelineGet->getData();

        $this->assertSame('field1', $result['my_custom_pipeline']['processors'][0]['kv']['field']);
        $this->assertSame('&', $result['my_custom_pipeline']['processors'][0]['kv']['field_split']);
        $this->assertSame('=', $result['my_custom_pipeline']['processors'][0]['kv']['value_split']);
    }
}
