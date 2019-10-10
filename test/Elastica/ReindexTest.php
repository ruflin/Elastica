<?php

namespace Elastica\Test;

use Elastica\Document;
use Elastica\Exception\ResponseException;
use Elastica\Index;
use Elastica\Query\Match;
use Elastica\Reindex;
use Elastica\Script\Script;
use Elastica\Type;

class ReindexTest extends Base
{
    /**
     * @group functional
     */
    public function testReindex()
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

    /**
     * @group functional
     */
    public function testReindexTypeOption()
    {
        $oldIndex = $this->_createIndex('', true, 2);
        $this->_addDocs($oldIndex, 10);

        $newIndex = $this->_createIndex(null, true, 2);

        $reindex = new Reindex($oldIndex, $newIndex, [
            Reindex::TYPE => '_doc',
        ]);
        $reindex->run();
        $newIndex->refresh();

        $this->assertEquals($oldIndex->count(), $newIndex->count());
    }

    /**
     * @group functional
     */
    public function testReindexOpTypeOptionWithProceedSetOnConflicts()
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

    /**
     * @group functional
     */
    public function testReindexOpTypeOptionWithProceedSetOnConflictStop()
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

        $response = $reindex->run();
        $newIndex->refresh();

        $this->assertEquals(5, $response->getData()['version_conflicts']);
    }

    /**
     * @group functional
     */
    public function testReindexWithQueryOption()
    {
        $oldIndex = $this->_createIndex('idx1', true, 2);
        $docs1 = $this->_addDocs($oldIndex, 10);

        $newIndex = $this->_createIndex('idx2', true, 2);

        $query = new Match('id', 8);

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

    /**
     * @group functional
     */
    public function testReindexWithSizeOption()
    {
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

    /**
     * @group functional
     */
    public function testReindexWithFalseSetOnWaitForCompletion()
    {
        $oldIndex = $this->_createIndex('idx1', true, 2);
        $this->_addDocs($oldIndex, 10);

        $newIndex = $this->_createIndex('idx2', true, 2);

        $reindex = new Reindex($oldIndex, $newIndex);
        $reindex->setWaitForCompletion(Reindex::WAIT_FOR_COMPLETION_FALSE);
        $reindex->run();

        $this->assertNotEmpty($reindex->getTaskId());

        $reindex = new Reindex($oldIndex, $newIndex);
        $reindex->setWaitForCompletion(false);
        $reindex->run();

        $this->assertNotEmpty($reindex->getTaskId());
    }

    /**
     * @group functional
     */
    public function testReindexWithScript()
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

    /**
     * @group functional
     */
    public function testReindexWithRemote()
    {
        $oldIndex = $this->_createIndex('idx1', true, 1);
        $newIndex = $this->_createIndex('idx2', true, 1);

        $reindex = new Reindex($oldIndex, $newIndex);
        $reindex->setParam(Reindex::REMOTE, [
            'host' => 'http://otherhost:9200',
        ]);

        try {
            $reindex->run();
            $this->assertFalse(true, 'Elasticsearch should have thrown an Exception, maybe the remote option has not been sent.');
        } catch (ResponseException $exception) {
            $this->assertContains('reindex.remote.whitelist', $exception->getMessage());
        }
    }

    private function _addDocs(Index $index, int $docs): array
    {
        $insert = [];
        for ($i = 1; $i <= $docs; ++$i) {
            $insert[] = new Document($i, ['id' => $i, 'key' => 'value']);
        }

        $index->addDocuments($insert);
        $index->refresh();

        return $insert;
    }
}
