<?php

namespace Elastica\Test;

use Elastica\Node;
use Elastica\Node\Info;
use Elastica\Node\Stats;
use Elastica\Test\Base as BaseTest;

/**
 * @internal
 */
class NodeTest extends BaseTest
{
    /**
     * @group functional
     */
    public function testCreateNode(): void
    {
        $client = $this->_getClient();
        $names = $client->getCluster()->getNodeNames();
        $name = \reset($names);

        $node = new Node($name, $client);
        $this->assertInstanceOf(Node::class, $node);
    }

    /**
     * @group functional
     */
    public function testGetInfo(): void
    {
        $client = $this->_getClient();
        $names = $client->getCluster()->getNodeNames();
        $name = \reset($names);

        $node = new Node($name, $client);

        $info = $node->getInfo();

        $this->assertInstanceOf(Info::class, $info);
    }

    /**
     * @group functional
     */
    public function testGetStats(): void
    {
        $client = $this->_getClient();
        $names = $client->getCluster()->getNodeNames();
        $name = \reset($names);

        $node = new Node($name, $client);

        $stats = $node->getStats();

        $this->assertInstanceOf(Stats::class, $stats);
    }

    /**
     * @group functional
     */
    public function testGetName(): void
    {
        $client = $this->_getClient();

        $nodes = $client->getCluster()->getNodes();
        // At least 1 instance must exist
        $this->assertGreaterThan(0, $nodes);

        $data = $client->request('_nodes')->getData();
        $rawNodes = $data['nodes'];

        foreach ($nodes as $node) {
            $this->assertEquals($rawNodes[$node->getId()]['name'], $node->getName());
        }
    }

    /**
     * @group functional
     */
    public function testGetId(): void
    {
        $node = new Node('Elastica', $this->_getClient());
    }
}
