<?php

declare(strict_types=1);

namespace Elastica\Test\Processor;

use Elastica\Bulk;
use Elastica\Document;
use Elastica\Processor\SetProcessor;
use Elastica\Test\BasePipeline as BasePipelineTest;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class SetProcessorTest extends BasePipelineTest
{
    #[Group('unit')]
    public function testSet(): void
    {
        $processor = new SetProcessor('field1', 582.1);

        $expected = [
            'set' => [
                'field' => 'field1',
                'value' => 582.1,
            ],
        ];

        $this->assertEquals($expected, $processor->toArray());
    }

    #[Group('unit')]
    public function testSetWithNonDefaultOptions(): void
    {
        $processor = (new SetProcessor('field1', 582.1))
            ->setOverride(false)
            ->setIgnoreFailure(true)
        ;

        $expected = [
            'set' => [
                'field' => 'field1',
                'value' => 582.1,
                'override' => false,
                'ignore_failure' => true,
            ],
        ];

        $this->assertEquals($expected, $processor->toArray());
    }

    #[Group('functional')]
    public function testSetField(): void
    {
        $set = new SetProcessor('package', 'Elastica');

        $pipeline = $this->_createPipeline('my_custom_pipeline', 'pipeline for Set');
        $pipeline->addProcessor($set)->create();

        $index = $this->_createIndex();
        $bulk = new Bulk($index->getClient());
        $bulk->setIndex($index);

        $bulk->addDocuments([
            new Document(null, ['name' => 'nicolas', 'package' => 'Elastico']),
            new Document(null, ['name' => 'ruflin']),
        ]);
        $bulk->setRequestParam('pipeline', 'my_custom_pipeline');

        $bulk->send();
        $index->refresh();

        $result = $index->search('*');

        $this->assertCount(2, $result->getResults());

        foreach ($result->getResults() as $rx) {
            $value = $rx->getData();
            $this->assertSame('Elastica', $value['package']);
        }
    }
}
