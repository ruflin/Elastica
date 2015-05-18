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

    public function testGetName()
    {
        $nodes = $this->_getClient()->getCluster()->getNodes();
        // At least 1 instance must exist
        $this->assertGreaterThan(0, $nodes);
        foreach ($nodes as $node) {
            $this->assertEquals($node->getName(), 'Elastica');
        }
    }
}
