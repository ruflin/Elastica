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
        $nodes = $this->_getClient()->getCluster()->getNodes();
        // At least 1 instance must exist
        $this->assertGreaterThan(0, $nodes);

        foreach ($nodes as $node) {
            $this->assertEquals($node->getName(), 'Elastica');
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
