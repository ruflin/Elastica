<?php

namespace Elastica\Test;

use Elastica\Client;
use Elastica\Exception\ResponseException;
use Elastica\Status;
use Elastica\Test\Base as BaseTest;

class StatusTest extends BaseTest
{
    public function testGetResponse()
    {
        $index = $this->_createIndex();
        $status = new Status($index->getClient());
        $this->assertInstanceOf('Elastica\Response', $status->getResponse());
    }

    public function testGetIndexStatuses()
    {
        $index = $this->_createIndex();

        $status = new Status($index->getClient());
        $statuses = $status->getIndexStatuses();

        $this->assertInternalType('array', $statuses);

        foreach ($statuses as $indexStatus) {
            $this->assertInstanceOf('Elastica\Index\Status', $indexStatus);
        }
    }

    public function testGetIndexNames()
    {
        $indexName = 'test';
        $client = $this->_getClient();
        $index = $client->getIndex($indexName);
        $index->create(array(), true);
        $index = $this->_createIndex();
        $index->refresh();
        $index->optimize();

        $status = new Status($index->getClient());
        $names = $status->getIndexNames();

        $this->assertInternalType('array', $names);
        $this->assertContains($index->getName(), $names);

        foreach ($names as $name) {
            $this->assertInternalType('string', $name);
        }
    }

    public function testIndexExists()
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

        $status->refresh();
        $this->assertTrue($status->indexExists($indexName));
    }

    public function testAliasExists()
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
        $this->assertEquals(array($indexName), array_map(
            function ($index) {
                return $index->getName();
            }, $indicesWithAlias));
    }

    public function testServerStatus()
    {
        $client = $this->_getClient();
        $status = $client->getStatus();
        $serverStatus = $status->getServerStatus();

        $this->assertTrue(!empty($serverStatus));
        $this->assertTrue('array' == gettype($serverStatus));
        $this->assertArrayHasKey('status', $serverStatus);
        $this->assertTrue($serverStatus['status'] == 200);
        $this->assertArrayHasKey('version', $serverStatus);

        $versionInfo = $serverStatus['version'];
        $this->assertArrayHasKey('number', $versionInfo);
    }
}
