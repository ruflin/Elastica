<?php

namespace Elastica\Test;

use Elastica\Client;
use Elastica\Node;
use Elastica\Test\Base as BaseTest;

class NodeTest extends BaseTest
{

    public function testCreateNode()
    {
        $client = $this->_getClient();
        $names = $client->getCluster()->getNodeNames();
        $name = reset($names);

        $node = new Node($name, $client);
        $this->assertInstanceOf('Elastica\Node', $node);
    }

    public function testGetInfo()
    {
        $client = $this->_getClient();
        $names = $client->getCluster()->getNodeNames();
        $name = reset($names);

        $node = new Node($name, $client);

        $info = $node->getInfo();

        $this->assertInstanceOf('Elastica\Node\Info', $info);
    }

    public function testGetStats()
    {
        $client = $this->_getClient();
        $names = $client->getCluster()->getNodeNames();
        $name = reset($names);

        $node = new Node($name, $client);

        $stats = $node->getStats();

        $this->assertInstanceOf('Elastica\Node\Stats', $stats);
    }

    /**
     * Shuts one of two nodes down (if two available)
     */
    public function testShutdown()
    {
        $this->markTestSkipped('At least two nodes have to be running, because 1 node is shutdown');
        $client = $this->_getClient();
        $nodes = $client->getCluster()->getNodes();

        $count = count($nodes);
        if ($count < 2) {
            $this->markTestSkipped('At least two nodes have to be running, because 1 node is shutdown');
        }

           // Store node info of node with port 9200 for later
        foreach ($nodes as $key => $node) {
            if ($node->getInfo()->getPort() == 9200) {
                $info = $node->getInfo();
                unset($nodes[$key]);
            }
        }

        // Select one of the not port 9200 nodes and shut it down
        $node = array_shift($nodes);
        $node->shutdown('2s');

        // Wait until node is shutdown
        sleep(5);

        // Use still existing node
        $client = new Client(array('host' => $info->getIp(), 'port' => $info->getPort()));
        $names = $client->getCluster()->getNodeNames();

        // One node less ...
        $this->assertEquals($count - 1, count($names));
    }
}
