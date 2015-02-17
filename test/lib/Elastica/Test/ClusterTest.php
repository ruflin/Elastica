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

        foreach ($cluster->getNodeNames() as $name) {
            $this->assertContains($name, array('Silver Fox', 'Skywalker', 'Wolverine'));
        }
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

    public function testGetIndexNames()
    {
        $client = $this->_getClient();
        $cluster = $client->getCluster();

        $index = $this->_createIndex();
        $index->delete();
        $cluster->refresh();

        // Checks that index does not exist
        $indexNames = $cluster->getIndexNames();
        $this->assertNotContains($index->getName(), $indexNames);

        $index = $this->_createIndex();
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
