<?php

namespace Elastica\Test;

use Elastica\Exception\ResponseException;
use Elastica\Response;
use Elastica\Status;
use Elastica\Test\Base as BaseTest;

/**
 * @internal
 */
class StatusTest extends BaseTest
{
    /**
     * @group functional
     */
    public function testGetResponse(): void
    {
        $index = $this->_createIndex();
        $status = new Status($index->getClient());
        $this->assertInstanceOf(Response::class, $status->getResponse());
    }

    /**
     * @group functional
     */
    public function testGetIndexNames(): void
    {
        $indexName = 'test';
        $client = $this->_getClient();
        $index = $client->getIndex($indexName);
        $index->create([], true);
        $index = $this->_createIndex();
        $index->refresh();
        $index->forcemerge();

        $status = new Status($index->getClient());
        $names = $status->getIndexNames();

        $this->assertIsArray($names);
        $this->assertContains($index->getName(), $names);

        foreach ($names as $name) {
            $this->assertIsString($name);
        }
    }

    /**
     * @group functional
     */
    public function testIndexExists(): void
    {
        $indexName = 'elastica_test';
        $aliasName = 'elastica_test-alias';

        $client = $this->_getClient();
        $index = $client->getIndex($indexName);

        try {
            // Make sure index is deleted first
            $index->delete();
        } catch (ResponseException $e) {
        }

        $status = new Status($client);
        $this->assertFalse($status->indexExists($indexName));
        $index->create();

        \usleep(10000);
        $status->refresh();
        $this->assertTrue($status->indexExists($indexName));
    }

    /**
     * @group functional
     */
    public function testAliasExists(): void
    {
        $aliasName = 'elastica_test-alias';

        $index1 = $this->_createIndex();
        $indexName = $index1->getName();

        $status = new Status($index1->getClient());

        foreach ($status->getIndicesWithAlias($aliasName) as $tmpIndex) {
            $tmpIndex->removeAlias($aliasName);
        }

        $this->assertFalse($status->aliasExists($aliasName));

        $index1->addAlias($aliasName);
        $status->refresh();
        $this->assertTrue($status->aliasExists($aliasName));

        $indicesWithAlias = $status->getIndicesWithAlias($aliasName);
        $this->assertEquals([$indexName], \array_map(
            function ($index) {
                return $index->getName();
            },
            $indicesWithAlias
        ));
    }
}
