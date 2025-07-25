<?php

declare(strict_types=1);

namespace Elastica\Test;

use Elastica\Node;
use Elastica\Test\Base as BaseTest;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class NodeTest extends BaseTest
{
    #[Group('functional')]
    public function testCreateNode(): void
    {
        $client = $this->_getClient();
        $names = $client->getCluster()->getNodeNames();
        $name = \reset($names);

        $node = new Node($name, $client);
        $this->assertSame($name, $node->getId());
    }

    #[Group('functional')]
    public function testGetInfo(): void
    {
        $client = $this->_getClient();
        $names = $client->getCluster()->getNodeNames();
        $name = \reset($names);

        $node = new Node($name, $client);

        $info = $node->getInfo();

        $this->assertSame($node, $info->getNode());
    }

    #[Group('functional')]
    public function testGetStats(): void
    {
        $client = $this->_getClient();
        $names = $client->getCluster()->getNodeNames();
        $name = \reset($names);

        $node = new Node($name, $client);

        $stats = $node->getStats();

        $this->assertSame($node, $stats->getNode());
    }

    #[Group('functional')]
    public function testGetName(): void
    {
        $client = $this->_getClient();

        $nodes = $client->getCluster()->getNodes();
        // At least 1 instance must exist
        $this->assertGreaterThan(0, $nodes);

        $data = $client->nodes()->info()->asArray();
        $rawNodes = $data['nodes'];

        foreach ($nodes as $node) {
            $this->assertEquals($rawNodes[$node->getId()]['name'], $node->getName());
        }
    }
}
