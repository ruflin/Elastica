<?php

namespace Elastica\Test\Processor;

use Elastica\Bulk;
use Elastica\Document;
use Elastica\Processor\FailProcessor;
use Elastica\Processor\JsonProcessor;
use Elastica\Test\BasePipeline as BasePipelineTest;

/**
 * @internal
 */
class FailProcessorTest extends BasePipelineTest
{
    /**
     * @group unit
     */
    public function testFail(): void
    {
        $processor = new FailProcessor('This is a custom fail message for processor');

        $expected = [
            'fail' => [
                'message' => 'This is a custom fail message for processor',
            ],
        ];

        $this->assertEquals($expected, $processor->toArray());
    }

    /**
     * @group unit
     */
    public function testFailWithNonDefaultOptions(): void
    {
        $processor = (new FailProcessor('This is a custom fail message for processor'))
            ->setIgnoreFailure(true)
        ;

        $expected = [
            'fail' => [
                'message' => 'This is a custom fail message for processor',
                'ignore_failure' => true,
            ],
        ];

        $this->assertEquals($expected, $processor->toArray());
    }

    /**
     * @group functional
     */
    public function testFailField(): void
    {
        $fail = new FailProcessor('custom error fail message');
        $json = new JsonProcessor('name');

        $pipeline = $this->_createPipeline('my_custom_pipeline', 'pipeline for Fail');
        $pipeline->addProcessor($json)->addProcessor($fail)->create();

        $index = $this->_createIndex();

        // Add document to normal index
        $doc1 = new Document(null, ['name' => '']);

        $bulk = new Bulk($index->getClient());
        $bulk->setIndex($index);

        $bulk->addDocument($doc1);
        $bulk->setRequestParam('pipeline', 'my_custom_pipeline');

        try {
            $bulk->send();
            $index->refresh();
            $this->fail('test should raise an exception!');
        } catch (\Exception $e) {
            $this->assertStringContainsString('custom error fail message', $e->getMessage());
        }
    }
}
