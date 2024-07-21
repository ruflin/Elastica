<?php

declare(strict_types=1);

namespace Elastica\Test\Processor;

use Elastica\Bulk;
use Elastica\Document;
use Elastica\Processor\JsonProcessor;
use Elastica\Test\BasePipeline as BasePipelineTest;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class JsonProcessorTest extends BasePipelineTest
{
    #[Group('unit')]
    public function testJson(): void
    {
        $processor = new JsonProcessor('string_source');

        $expected = [
            'json' => [
                'field' => 'string_source',
            ],
        ];

        $this->assertEquals($expected, $processor->toArray());
    }

    #[Group('unit')]
    public function testJsonWithNonDefaultOptions(): void
    {
        $processor = (new JsonProcessor('string_source'))
            ->setIgnoreFailure(true)
            ->setTargetField('json_target')
            ->setAddToRoot(true)
        ;

        $expected = [
            'json' => [
                'field' => 'string_source',
                'ignore_failure' => true,
                'target_field' => 'json_target',
                'add_to_root' => true,
            ],
        ];

        $this->assertEquals($expected, $processor->toArray());
    }

    #[Group('functional')]
    public function testJsonField(): void
    {
        $json = new JsonProcessor('name');
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
