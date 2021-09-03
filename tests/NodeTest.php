<?php

namespace Elastica\Test;

use Elastica\Node;
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
        $this->assertSame($name, $node->getId());
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

        $this->assertSame($node, $info->getNode());
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

        $this->assertSame($node, $stats->getNode());
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
}
