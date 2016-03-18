<?php

namespace Elastica\Test;

use Elastica\Node;
use Elastica\Test\Base as BaseTest;

class NodeTest extends BaseTest
{
    /**
     * @group functional
     */
    public function testCreateNode()
    {
        $client = $this->_getClient();
        $names = $client->getCluster()->getNodeNames();
        $name = reset($names);

        $node = new Node($name, $client);
        $this->assertInstanceOf('Elastica\Node', $node);
    }

    /**
     * @group functional
     */
    public function testGetInfo()
    {
        $client = $this->_getClient();
        $names = $client->getCluster()->getNodeNames();
        $name = reset($names);

        $node = new Node($name, $client);

        $info = $node->getInfo();

        $this->assertInstanceOf('Elastica\Node\Info', $info);
    }

    /**
     * @group functional
     */
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
     * @group functional
     */
    public function testGetName()
    {
        $client = $this->_getClient();

        $nodes = $client->getCluster()->getNodes();
        // At least 1 instance must exist
        $this->assertGreaterThan(0, $nodes);

        $data = $client->request('/_nodes')->getData();
        $rawNodes = $data['nodes'];

        foreach ($nodes as $node) {
            $this->assertEquals($rawNodes[$node->getId()]['name'], $node->getName());
        }
    }

    /**
     * @group functional
     */
    public function testGetId()
    {
        $node = new Node('Elastica', $this->_getClient());
    }
}
