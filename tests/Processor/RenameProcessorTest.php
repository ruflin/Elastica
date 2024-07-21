<?php

declare(strict_types=1);

namespace Elastica\Test\Processor;

use Elastica\Bulk;
use Elastica\Document;
use Elastica\Processor\RenameProcessor;
use Elastica\Test\BasePipeline as BasePipelineTest;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class RenameProcessorTest extends BasePipelineTest
{
    #[Group('unit')]
    public function testRename(): void
    {
        $processor = new RenameProcessor('foo', 'foobar');

        $expected = [
            'rename' => [
                'field' => 'foo',
                'target_field' => 'foobar',
            ],
        ];

        $this->assertEquals($expected, $processor->toArray());
    }

    #[Group('unit')]
    public function testRenameWithNonDefaultOptions(): void
    {
        $processor = (new RenameProcessor('foo', 'foobar'))
            ->setIgnoreFailure(true)
            ->setIgnoreMissing(true)
        ;

        $expected = [
            'rename' => [
                'field' => 'foo',
                'target_field' => 'foobar',
                'ignore_failure' => true,
                'ignore_missing' => true,
            ],
        ];

        $this->assertEquals($expected, $processor->toArray());
    }

    #[Group('functional')]
    public function testRenameField(): void
    {
        $rename = new RenameProcessor('package', 'packages');

        $pipeline = $this->_createPipeline('my_custom_pipeline', 'pipeline for Rename');
        $pipeline->addProcessor($rename)->create();

        $index = $this->_createIndex();
        $bulk = new Bulk($index->getClient());
        $bulk->setIndex($index);

        $bulk->addDocuments([
            new Document(null, ['name' => 'nicolas', 'package' => 'Elastico']),
        ]);
        $bulk->setRequestParam('pipeline', 'my_custom_pipeline');

        $bulk->send();
        $index->refresh();

        $result = $index->search('*');

        $this->assertCount(1, $result->getResults());

        $results = $result->getResults();
        $this->assertArrayHasKey('packages', $results[0]->getHit()['_source']);
    }
}
