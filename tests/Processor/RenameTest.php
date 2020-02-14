<?php

namespace Elastica\Test\Processor;

use Elastica\Bulk;
use Elastica\Document;
use Elastica\Processor\Rename;
use Elastica\ResultSet;
use Elastica\Test\BasePipeline as BasePipelineTest;

/**
 * @internal
 */
class RenameTest extends BasePipelineTest
{
    /**
     * @group unit
     */
    public function testRename(): void
    {
        $processor = new Rename('foo', 'foobar');

        $expected = [
            'rename' => [
                'field' => 'foo',
                'target_field' => 'foobar',
            ],
        ];

        $this->assertEquals($expected, $processor->toArray());
    }

    /**
     * @group unit
     */
    public function testRenameWithNonDefaultOptions(): void
    {
        $processor = new Rename('foo', 'foobar');
        $processor->setIgnoreMissing(true);

        $expected = [
            'rename' => [
                'field' => 'foo',
                'target_field' => 'foobar',
                'ignore_missing' => true,
            ],
        ];

        $this->assertEquals($expected, $processor->toArray());
    }

    /**
     * @group functional
     */
    public function testRenameField(): void
    {
        $rename = new Rename('package', 'packages');

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

        /** @var ResultSet $result */
        $result = $index->search('*');

        $this->assertCount(1, $result->getResults());

        $results = $result->getResults();
        $this->assertArrayHasKey('packages', ($results[0]->getHit())['_source']);
    }
}
