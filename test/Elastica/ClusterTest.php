<?php

namespace Elastica\Test;

use Elastica\Cluster;
use Elastica\Cluster\Health;
use Elastica\Node;
use Elastica\Test\Base as BaseTest;
use Yoast\PHPUnitPolyfills\Polyfills\AssertIsType;

class ClusterTest extends BaseTest
{
    use AssertIsType;

    /**
     * @group functional
     */
    public function testGetNodeNames()
    {
        $client = $this->_getClient();
        $data = $client->request('/')->getData();

        $cluster = new Cluster($client);

        $data = $client->request('_nodes')->getData();
        $rawNodes = $data['nodes'];

        $nodeNames = $cluster->getNodeNames();
        $rawNodeNames = [];

        foreach ($rawNodes as $rawNode) {
            $rawNodeNames[] = $rawNode['name'];
        }

        $this->assertEquals(asort($rawNodeNames), asort($nodeNames));
    }

    /**
     * @group functional
     */
    public function testGetNodes()
    {
        $client = $this->_getClient();
        $cluster = $client->getCluster();

        $nodes = $cluster->getNodes();

        $this->assertContainsOnlyInstancesOf(Node::class, $nodes);
        $this->assertGreaterThan(0, count($nodes));
    }

    /**
     * @group functional
     */
    public function testGetState()
    {
        $client = $this->_getClient();
        $cluster = $client->getCluster();
        $state = $cluster->getState();
        self::assertIsArray($state);
    }

    /**
     * @group functional
     */
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

    /**
     * @group functional
     */
    public function testGetHealth()
    {
        $client = $this->_getClient();
        $this->assertInstanceOf(Health::class, $client->getCluster()->getHealth());
    }
}
