<?php

namespace Elastica\Test\Processor;

use Elastica\Bulk;
use Elastica\Document;
use Elastica\Processor\Json;
use Elastica\ResultSet;
use Elastica\Test\BasePipeline as BasePipelineTest;

/**
 * @internal
 */
class JsonTest extends BasePipelineTest
{
    /**
     * @group unit
     */
    public function testJson(): void
    {
        $processor = new Json('string_source');

        $expected = [
            'json' => [
                'field' => 'string_source',
            ],
        ];

        $this->assertEquals($expected, $processor->toArray());
    }

    /**
     * @group unit
     */
    public function testJsonWithNonDefaultOptions(): void
    {
        $processor = new Json('string_source');
        $processor->setTargetField('json_target');
        $processor->setAddToRoot(true);

        $expected = [
            'json' => [
                'field' => 'string_source',
                'target_field' => 'json_target',
                'add_to_root' => true,
            ],
        ];

        $this->assertEquals($expected, $processor->toArray());
    }

    /**
     * @group functional
     */
    public function testJsonField(): void
    {
        $json = new Json('name');
        $json->setTargetField('realname');

        $pipeline = $this->_createPipeline('my_custom_pipeline', 'pipeline for Json');
        $pipeline->addProcessor($json)->create();

        $index = $this->_createIndex();
        $bulk = new Bulk($index->getClient());
        $bulk->setIndex($index);

        $bulk->addDocument(new Document(null, ['name' => \json_encode(['foo' => 2000])]));
        $bulk->setRequestParam('pipeline', 'my_custom_pipeline');

        $bulk->send();
        $index->refresh();

        /** @var ResultSet $result */
        $result = $index->search('*');

        $this->assertCount(1, $result->getResults());

        $resultExpected = [
            'foo' => 2000,
        ];

        foreach ($result->getResults() as $rx) {
            $value = $rx->getData();
            $this->assertEquals($resultExpected, $value['realname']);
        }
    }
}
