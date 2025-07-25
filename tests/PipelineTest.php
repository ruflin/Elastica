<?php

declare(strict_types=1);

namespace Elastica\Test;

use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastica\Bulk;
use Elastica\Client;
use Elastica\Document;
use Elastica\Pipeline;
use Elastica\Processor\RenameProcessor;
use Elastica\Processor\SetProcessor;
use Elastica\Processor\TrimProcessor;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class PipelineTest extends BasePipeline
{
    #[Group('unit')]
    public function testProcessor(): void
    {
        $trim = new TrimProcessor('field1');
        $rename = new RenameProcessor('foo', 'target.field');
        $set = new SetProcessor('field4', 324);

        $client = $this->createMock(Client::class);
        $processors = new Pipeline($client);
        $processors->setDescription('this is a new pipeline');
        $processors->addProcessor($trim);
        $processors->addProcessor($rename);
        $processors->addProcessor($set);

        $expected = [
            'description' => 'this is a new pipeline',
            'processors' => [
                [
                    'trim' => [
                        'field' => 'field1',
                    ],
                ],
                [
                    'rename' => [
                        'field' => 'foo',
                        'target_field' => 'target.field',
                    ],
                ],
                [
                    'set' => [
                        'field' => 'field4',
                        'value' => 324,
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $processors->toArray());
    }

    #[Group('functional')]
    public function testPipelineCreate(): void
    {
        $set = new SetProcessor('field4', 333);
        $trim = new TrimProcessor('field1');
        $rename = new RenameProcessor('foo', 'target.field');

        $pipeline = $this->_createPipeline('my_custom_pipeline', 'pipeline for Set');
        $pipeline->addProcessor($set);
        $pipeline->addProcessor($trim);
        $pipeline->addProcessor($rename);

        $result = $pipeline->create();

        $this->assertArrayHasKey('acknowledged', $result->getData());
        $this->assertTrue($result->getData()['acknowledged']);

        $pipeGet = $pipeline->getPipeline('my_custom_pipeline');
        $result = $pipeGet->getData();

        $this->assertSame('pipeline for Set', $result['my_custom_pipeline']['description']);
        $this->assertSame('field4', $result['my_custom_pipeline']['processors'][0]['set']['field']);
        $this->assertSame(333, $result['my_custom_pipeline']['processors'][0]['set']['value']);
        $this->assertSame('field1', $result['my_custom_pipeline']['processors'][1]['trim']['field']);
        $this->assertSame('foo', $result['my_custom_pipeline']['processors'][2]['rename']['field']);
    }

    #[Group('functional')]
    public function testPipelineonIndex(): void
    {
        $set = new SetProcessor('foo', 333);

        $pipeline = $this->_createPipeline('my_custom_pipeline', 'pipeline for Set');
        $pipeline->addProcessor($set);

        $result = $pipeline->create();

        $this->assertArrayHasKey('acknowledged', $result->getData());
        $this->assertTrue($result->getData()['acknowledged']);

        $index = $this->_createIndex('testpipelinecreation');

        // Add document to normal index
        $doc1 = new Document(null, ['name' => 'ruflin', 'type' => 'elastica', 'foo' => null]);
        $doc2 = new Document(null, ['name' => 'nicolas', 'type' => 'elastica', 'foo' => null]);

        $bulk = new Bulk($index->getClient());
        $bulk->setIndex($index);

        $bulk->addDocuments([
            $doc1, $doc2,
        ]);
        $bulk->setRequestParam('pipeline', 'my_custom_pipeline');

        $bulk->send();
        $index->refresh();

        $result = $index->search('elastica');

        $this->assertCount(2, $result->getResults());

        foreach ($result->getResults() as $rx) {
            $value = $rx->getData();
            $this->assertEquals(333, $value['foo']);
        }
    }

    #[Group('functional')]
    public function testDeletePipeline(): void
    {
        $pipeline = $this->_createPipeline();
        try {
            $pipeline->deletePipeline('non_existent_pipeline');
            $this->fail('an exception should be raised!');
        } catch (ClientResponseException $e) {
            $error = \json_decode((string) $e->getResponse()->getBody(), true)['error']['root_cause'][0] ?? null;

            $this->assertEquals('resource_not_found_exception', $error['type']);
            $this->assertEquals('pipeline [non_existent_pipeline] is missing', $error['reason']);
        }
    }
}
