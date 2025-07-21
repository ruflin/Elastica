<?php

declare(strict_types=1);

namespace Elastica\Test;

use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastica\Document;
use Elastica\Index;
use Elastica\Pipeline;
use Elastica\Processor\RenameProcessor;
use Elastica\Processor\UppercaseProcessor;
use Elastica\Query\MatchQuery;
use Elastica\Reindex;
use Elastica\Script\Script;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class ReindexTest extends Base
{
    #[Group('functional')]
    public function testReindex(): void
    {
        $oldIndex = $this->_createIndex('idx1', true, 2);
        $this->_addDocs($oldIndex, 10);

        $newIndex = $this->_createIndex('idx2', true, 2);

        $reindex = new Reindex($oldIndex, $newIndex);
        $response = $reindex->run();
        $newIndex->refresh();

        $this->assertEquals($oldIndex->count(), $newIndex->count());
        $this->assertEquals($oldIndex->count(), $response->getData()['created']);
    }

    #[Group('functional')]
    public function testReindexOpTypeOptionWithProceedSetOnConflicts(): void
    {
        $oldIndex = $this->_createIndex('idx1', true, 2);
        $docs1 = $this->_addDocs($oldIndex, 10);

        $subDocs1 = \array_splice($docs1, 0, 5);

        $newIndex = $this->_createIndex('idx2', true, 2);
        $newIndex->addDocuments($subDocs1);
        $newIndex->refresh();

        $this->assertEquals(5, $newIndex->count());

        $reindex = new Reindex($oldIndex, $newIndex, [
            Reindex::OPERATION_TYPE => Reindex::OPERATION_TYPE_CREATE,
            Reindex::CONFLICTS => Reindex::CONFLICTS_PROCEED,
        ]);

        $reindex->run();
        $newIndex->refresh();

        $this->assertEquals($oldIndex->count(), $newIndex->count());
    }

    #[Group('functional')]
    public function testReindexOpTypeOptionWithProceedSetOnConflictStop(): void
    {
        $oldIndex = $this->_createIndex('idx1', true, 2);
        $docs1 = $this->_addDocs($oldIndex, 10);

        $subDocs1 = \array_splice($docs1, 0, 5);

        $newIndex = $this->_createIndex('idx2', true, 2);
        $newIndex->addDocuments($subDocs1);
        $newIndex->refresh();

        $this->assertEquals(5, $newIndex->count());

        $reindex = new Reindex($oldIndex, $newIndex, [
            Reindex::OPERATION_TYPE => Reindex::OPERATION_TYPE_CREATE,
        ]);

        try {
            $reindex->run();

            $this->fail('Elasticsearch should have thrown an Exception.');
        } catch (ClientResponseException $e) {
            $newIndex->refresh();

            $this->assertEquals(5, \json_decode($e->getResponse()->getBody()->__toString(), true)['version_conflicts']);
        }
    }

    #[Group('functional')]
    public function testReindexWithQueryOption(): void
    {
        $oldIndex = $this->_createIndex('idx1', true, 2);
        $docs1 = $this->_addDocs($oldIndex, 10);

        $newIndex = $this->_createIndex('idx2', true, 2);

        $query = new MatchQuery('id', 8);

        $reindex = new Reindex($oldIndex, $newIndex, [
            Reindex::QUERY => $query,
        ]);
        $reindex->run();
        $newIndex->refresh();

        $results = $newIndex->search()->getResults();
        $this->assertEquals(1, $newIndex->count());
        foreach ($results as $result) {
            $this->assertEquals($docs1[7]->getData(), $result->getData());
        }
    }

    #[Group('functional')]
    public function testReindexWithSizeOption(): void
    {
        // @see https://www.elastic.co/guide/en/elasticsearch/reference/master/migrating-8.0.html#breaking-changes-8.0
        $isEs8 = \version_compare($_SERVER['ES_VERSION'], '8.0.0', '>=');
        if ($isEs8) {
            $this->markTestSkipped('In the reindex, delete by query, and update by query APIs, the size parameter has been renamed.');
        }

        $oldIndex = $this->_createIndex('idx1', true, 2);
        $this->_addDocs($oldIndex, 10);

        $newIndex = $this->_createIndex('idx2', true, 2);

        $reindex = new Reindex($oldIndex, $newIndex, [
            Reindex::SIZE => 5,
        ]);
        $reindex->run();
        $newIndex->refresh();

        $this->assertEquals(5, $newIndex->count());
    }

    #[Group('functional')]
    public function testReindexWithFalseSetOnWaitForCompletion(): void
    {
        $oldIndex = $this->_createIndex('idx1', true, 2);
        $this->_addDocs($oldIndex, 10);

        $newIndex = $this->_createIndex('idx2', true, 2);

        $reindex = new Reindex($oldIndex, $newIndex);
        $reindex->setWaitForCompletion(false);
        $reindex->run();

        $this->assertNotEmpty($reindex->getTaskId());
    }

    #[Group('functional')]
    public function testReindexWithScript(): void
    {
        $oldIndex = $this->_createIndex('idx1', true, 2);
        $this->_addDocs($oldIndex, 10);

        $newIndex = $this->_createIndex('idx2', true, 2);

        $reindex = new Reindex($oldIndex, $newIndex);
        $script = new Script('ctx._source.remove(\'id\')');

        $reindex->setScript($script);

        $reindex->run();
        $newIndex->refresh();

        $results = $newIndex->search()->getResults();
        $this->assertEquals(10, $newIndex->count());

        foreach ($results as $result) {
            $this->assertArrayNotHasKey('id', $result->getData());
        }
    }

    #[Group('functional')]
    public function testReindexWithRemote(): void
    {
        $oldIndex = $this->_createIndex('idx1', true, 1);
        $newIndex = $this->_createIndex('idx2', true, 1);

        $reindex = new Reindex($oldIndex, $newIndex);
        $reindex->setParam(Reindex::REMOTE, [
            'host' => 'http://otherhost:9200',
        ]);

        try {
            $reindex->run();

            $this->fail('Elasticsearch should have thrown an Exception, maybe the remote option has not been sent.');
        } catch (ClientResponseException $exception) {
            $this->assertStringContainsString('reindex.remote.whitelist', $exception->getMessage());
        }
    }

    #[Group('functional')]
    public function testReindexWithPipeline(): void
    {
        $oldIndex = $this->_createIndex('idx1', true, 2);
        $this->_addDocs($oldIndex, 10);

        $newIndex = $this->_createIndex('idx2', true, 2);

        $pipeline = new Pipeline($newIndex->getClient());
        $pipeline->setId('my-pipeline');
        $pipeline->setDescription('For testing purposes"');
        $pipeline->addProcessor(new RenameProcessor('id', 'identifier'));
        $pipeline->addProcessor(new UppercaseProcessor('key'));

        $reindex = new Reindex($oldIndex, $newIndex);
        $reindex->setPipeline($pipeline);

        $pipeline->create();
        $reindex->run();
        $newIndex->refresh();

        $results = $newIndex->search()->getResults();
        $this->assertEquals(10, $newIndex->count());

        foreach ($results as $result) {
            $this->assertArrayNotHasKey('id', $result->getData());
            $this->assertArrayHasKey('identifier', $result->getData());
            $this->assertSame('VALUE', $result->getData()['key']);
        }
    }

    #[Group('functional')]
    public function testReindexWithRefresh(): void
    {
        $oldIndex = $this->_createIndex('idx1', true, 2);
        $this->_addDocs($oldIndex, 10);

        $newIndex = $this->_createIndex('idx2', true, 2);

        $reindex = new Reindex($oldIndex, $newIndex);
        $reindex->setRefresh(Reindex::REFRESH_TRUE);

        $reindex->run();

        $newIndex->search()->getResults();
        $this->assertEquals(10, $newIndex->count());
    }

    private function _addDocs(Index $index, int $docs): array
    {
        $insert = [];
        for ($i = 1; $i <= $docs; ++$i) {
            $insert[] = new Document((string) $i, ['id' => $i, 'key' => 'value']);
        }

        $index->addDocuments($insert);
        $index->refresh();

        return $insert;
    }
}
