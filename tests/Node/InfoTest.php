<?php

namespace Elastica\Test\Node;

use Elastica\Node;
use Elastica\Node\Info as NodeInfo;
use Elastica\Test\Base as BaseTest;

/**
 * @internal
 */
class InfoTest extends BaseTest
{
    /**
     * @group functional
     */
    public function testGet(): void
    {
        $client = $this->_getClient();
        $names = $client->getCluster()->getNodeNames();
        $name = \reset($names);

        $node = new Node($name, $client);
        $info = new NodeInfo($node);

        $this->assertNull($info->get('os', 'mem', 'total'));

        // Load os infos
        $info = new NodeInfo($node, ['os', 'process', 'jvm']);

        $this->assertNotNull($info->get('os', 'name'));
        $this->assertNotNull($info->get('process', 'id'));
        $this->assertNotNull($info->get('jvm', 'mem', 'heap_init_in_bytes'));
        $this->assertIsArray($info->get('jvm', 'mem'));
        $this->assertNull($info->get('test', 'notest', 'notexist'));
    }

    /**
     * @group functional
     */
    public function testHasPlugin(): void
    {
        $client = $this->_getClient();
        $nodes = $client->getCluster()->getNodes();
        $node = $nodes[0];
        $info = $node->getInfo();

        $this->assertFalse($info->hasPlugin('foo'));
        $this->assertTrue($info->hasPlugin('ingest-attachment'));
    }

    /**
     * @group functional
     */
    public function testGetId(): void
    {
        $client = $this->_getClient();
        $nodes = $client->getCluster()->getNodes();

        $ids = [];

        foreach ($nodes as $node) {
            $id = $node->getInfo()->getId();

            // Checks that the ids are unique
            $this->assertNotContains($id, $ids);
            $ids[] = $id;
        }
    }

    /**
     * @group functional
     */
    public function testGetName(): void
    {
        $client = $this->_getClient();

        $data = $client->request('_nodes/stats')->getData();
        $rawNodes = $data['nodes'];

        $nodes = $client->getCluster()->getNodes();

        foreach ($nodes as $node) {
            $this->assertEquals($rawNodes[$node->getId()]['name'], $node->getInfo()->getName());
        }
    }

    /**
     * @group functional
     */
    public function testParams(): void
    {
        $client = $this->_getClient();

        $info = $client->getCluster()->getNodes()[0]->getInfo();

        $this->assertArrayHasKey('plugins', $info->getData());
        $info->refresh(['jvm']);
        $this->assertArrayNotHasKey('plugins', $info->getData());
    }
}
