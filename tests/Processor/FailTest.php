<?php

namespace Elastica\Test\Processor;

use Elastica\Bulk;
use Elastica\Document;
use Elastica\Processor\Fail;
use Elastica\Processor\Json;

/**
 * @internal
 */
class FailTest extends BaseProcessorTestCase
{
    /**
     * @group unit
     */
    public function testFail(): void
    {
        $processor = new Fail('This is a custom fail message for processor');

        $expected = [
            'fail' => [
                'message' => 'This is a custom fail message for processor',
            ],
        ];

        $this->assertEquals($expected, $processor->toArray());
    }

    /**
     * @group functional
     */
    public function testFailField(): void
    {
        $fail = new Fail('custom error fail message');
        $json = new Json('name');

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

    public function validProcessorProvider(): array
    {
        return [
            [new Fail('message')],
        ];
    }
}
