<?php

namespace Elastica\Test\Processor;

use Elastica\Bulk;
use Elastica\Document;
use Elastica\Processor\RemoveProcessor;
use Elastica\Test\BasePipeline as BasePipelineTest;

/**
 * @internal
 */
class RemoveProcessorTest extends BasePipelineTest
{
    /**
     * @group unit
     */
    public function testRemove(): void
    {
        $processor = new RemoveProcessor('foo');

        $expected = [
            'remove' => [
                'field' => 'foo',
            ],
        ];

        $this->assertEquals($expected, $processor->toArray());
    }

    /**
     * @group unit
     */
    public function testRemoveArray(): void
    {
        $processor = new RemoveProcessor(['foo', 'bar']);

        $expected = [
            'remove' => [
                'field' => ['foo', 'bar'],
            ],
        ];

        $this->assertEquals($expected, $processor->toArray());
    }

    /**
     * @group functional
     */
    public function testRemoveField(): void
    {
        $remove = new RemoveProcessor(['es_version', 'package']);

        $pipeline = $this->_createPipeline('my_custom_pipeline', 'pipeline for Remove');
        $pipeline->addProcessor($remove)->create();

        $index = $this->_createIndex();
        $bulk = new Bulk($index->getClient());
        $bulk->setIndex($index);

        $bulk->addDocuments([
            new Document(null, ['name' => 'nicolas', 'es_version' => 6, 'package' => 'Elastica']),
            new Document(null, ['name' => 'ruflin', 'es_version' => 5, 'package' => 'Elastica_old']),
        ]);
        $bulk->setRequestParam('pipeline', 'my_custom_pipeline');

        $bulk->send();
        $index->refresh();

        $result = $index->search('*');

        $this->assertCount(2, $result->getResults());

        foreach ($result->getResults() as $rx) {
            $value = $rx->getData();
            $this->assertArrayNotHasKey('package', $value);
            $this->assertArrayNotHasKey('es_version', $value);
        }
    }
}
