<?php

declare(strict_types=1);

namespace Elastica\Test\Processor;

use Elastica\Bulk;
use Elastica\Document;
use Elastica\Processor\AppendProcessor;
use Elastica\Test\BasePipeline as BasePipelineTest;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class AppendProcessorTest extends BasePipelineTest
{
    #[Group('unit')]
    public function testAppendSingleValue(): void
    {
        $processor = new AppendProcessor('field1', 'item2');

        $expected = [
            'append' => [
                'field' => 'field1',
                'value' => 'item2',
            ],
        ];

        $this->assertEquals($expected, $processor->toArray());
    }

    #[Group('unit')]
    public function testAppendArray(): void
    {
        $processor = new AppendProcessor('field1', ['item2', 'item3', 'item4']);

        $expected = [
            'append' => [
                'field' => 'field1',
                'value' => ['item2', 'item3', 'item4'],
            ],
        ];

        $this->assertEquals($expected, $processor->toArray());
    }

    #[Group('functional')]
    public function testAppend(): void
    {
        $append = new AppendProcessor('foo', ['item2', 'item3', 'item4']);

        $pipeline = $this->_createPipeline('my_custom_pipeline', 'pipeline for Append');
        $pipeline->addProcessor($append)->create();

        $index = $this->_createIndex();
        $bulk = new Bulk($index->getClient());
        $bulk->setIndex($index);

        $bulk->addDocuments([
            new Document(null, ['name' => 'ruflin', 'type' => 'elastica', 'foo' => null]),
            new Document(null, ['name' => 'nicolas', 'type' => 'elastica', 'foo' => null]),
        ]);
        $bulk->setRequestParam('pipeline', 'my_custom_pipeline');

        $bulk->send();
        $index->refresh();

        $result = $index->search('*');

        $this->assertCount(2, $result->getResults());

        foreach ($result->getResults() as $rx) {
            $value = $rx->getData();
            $this->assertCount(4, $value['foo']);
            $this->assertNull($value['foo'][0]);
            $this->assertEquals('item2', $value['foo'][1]);
            $this->assertEquals('item3', $value['foo'][2]);
            $this->assertEquals('item4', $value['foo'][3]);
        }
    }
}
