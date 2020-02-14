<?php

namespace Elastica\Test\Processor;

use Elastica\Processor\Kv;
use Elastica\Test\BasePipeline as BasePipelineTest;

/**
 * @internal
 */
class KvTest extends BasePipelineTest
{
    /**
     * @group unit
     */
    public function testKv(): void
    {
        $processor = new Kv('message', ' ', '=');

        $expected = [
            'kv' => [
                'field' => 'message',
                'field_split' => ' ',
                'value_split' => '=',
            ],
        ];

        $this->assertEquals($expected, $processor->toArray());
    }

    /**
     * @group unit
     */
    public function testKvWithNonDefaultOptions(): void
    {
        $processor = new Kv('message', ' ', '=');
        $processor->setTargetField('target_field');
        $processor->setIncludeKeys(['l1', 'l2']);
        $processor->setExcludeKeys(['l4', 'l5']);
        $processor->setIgnoreMissing(true);

        $expected = [
            'kv' => [
                'field' => 'message',
                'field_split' => ' ',
                'value_split' => '=',
                'target_field' => 'target_field',
                'include_keys' => ['l1', 'l2'],
                'exclude_keys' => ['l4', 'l5'],
                'ignore_missing' => true,
            ],
        ];

        $this->assertEquals($expected, $processor->toArray());
    }

    /**
     * @group functional
     */
    public function testKVIncludeExludeKeys(): void
    {
        $kv = new Kv('field1', '&', '=');
        $kv->setExcludeKeys(['second']);

        $pipeline = $this->_createPipeline('my_custom_pipeline', 'pipeline for KV');
        $pipeline->addProcessor($kv);

        $result = $pipeline->create();

        $this->assertContains('acknowledged', $result->getData());

        $pipelineGet = $pipeline->getPipeline('my_custom_pipeline');
        $result = $pipelineGet->getData();

        $this->assertSame($result['my_custom_pipeline']['processors'][0]['kv']['field'], 'field1');
        $this->assertSame($result['my_custom_pipeline']['processors'][0]['kv']['field_split'], '&');
        $this->assertSame($result['my_custom_pipeline']['processors'][0]['kv']['value_split'], '=');
    }
}
