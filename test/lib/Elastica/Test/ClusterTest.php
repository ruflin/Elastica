<?php

namespace Elastica\Test;

use Elastica\Client;
use Elastica\Cluster;
use Elastica\Test\Base as BaseTest;

class ClusterTest extends BaseTest
{

    public function testGetNodeNames()
    {
        $client = $this->_getClient();

        $cluster = new Cluster($client);

        $names = $cluster->getNodeNames();

        $this->assertInternalType('array', $names);
        $this->assertGreaterThan(0, count($names));
    }

    public function testGetNodes()
    {
        $client = $this->_getClient();
        $cluster = $client->getCluster();

        $nodes = $cluster->getNodes();

        foreach ($nodes as $node) {
            $this->assertInstanceOf('Elastica\Node', $node);
        }

        $this->assertGreaterThan(0, count($nodes));
    }

    public function testGetState()
    {
        $client = $this->_getClient();
        $cluster = $client->getCluster();
        $state = $cluster->getState();
        $this->assertInternalType('array', $state);
    }

    /**
     * @expectedException \Elastica\Exception\ConnectionException
     */
    public function testShutdown()
    {
        $this->markTestSkipped('This test shuts down the cluster which means the following tests would not work');
        $client = $this->_getClient();
        $cluster = $client->getCluster();

        $cluster->shutdown('2s');

        sleep(5);

        $client->getStatus();
    }

    public function testGetIndexNames()
    {
        $client = $this->_getClient();
        $cluster = $client->getCluster();

        $indexName = 'elastica_test999';
        $index = $this->_createIndex($indexName);
        $index->delete();
        $cluster->refresh();

        // Checks that index does not exist
        $indexNames = $cluster->getIndexNames();
        $this->assertNotContains($index->getName(), $indexNames);

        $index = $this->_createIndex($indexName);
        $cluster->refresh();

        // Now index should exist
        $indexNames = $cluster->getIndexNames();
        $this->assertContains($index->getname(), $indexNames);
    }

    public function testGetHealth()
    {
        $client = $this->_getClient();
        $this->assertInstanceOf('Elastica\Cluster\Health', $client->getCluster()->getHealth());
    }
}
