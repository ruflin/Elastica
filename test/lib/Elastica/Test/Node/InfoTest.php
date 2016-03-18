<?php

namespace Elastica\Test\Node;

use Elastica\Node;
use Elastica\Node\Info as NodeInfo;
use Elastica\Test\Base as BaseTest;

class InfoTest extends BaseTest
{
    /**
     * @group functional
     */
    public function testGet()
    {
        $client = $this->_getClient();
        $names = $client->getCluster()->getNodeNames();
        $name = reset($names);

        $node = new Node($name, $client);
        $info = new NodeInfo($node);

        $this->assertNull($info->get('os', 'mem', 'total'));

        // Load os infos
        $info = new NodeInfo($node, array('os'));

        $this->assertNotNull($info->get('os', 'name'));
        $this->assertNotNull($info->get('process', 'id'));
        $this->assertNotNull($info->get('jvm', 'mem', 'heap_init_in_bytes'));
        $this->assertInternalType('array', $info->get('jvm', 'mem'));
        $this->assertNull($info->get('test', 'notest', 'notexist'));
    }

    /**
     * @group functional
     */
    public function testHasPlugin()
    {
        $client = $this->_getClient();
        $nodes = $client->getCluster()->getNodes();
        $node = $nodes[0];
        $info = $node->getInfo();

        $this->assertFalse($info->hasPlugin('foo'));

        $data = $client->request('/_nodes')->getData();
        $rawNode = array_pop($data['nodes']);

        if (count($rawNode['plugins']) == 0) {
            $this->markTestIncomplete('No plugins installed, can\'t test hasPlugin');
        }

        $this->assertTrue($info->hasPlugin($rawNode['plugins'][0]['name']));
    }

    /**
     * @group functional
     */
    public function testGetId()
    {
        $client = $this->_getClient();
        $nodes = $client->getCluster()->getNodes();

        $ids = array();

        foreach ($nodes as $node) {
            $id = $node->getInfo()->getId();

            // Checks that the ids are unique
            $this->assertFalse(in_array($id, $ids));
            $ids[] = $id;
        }
    }

    /**
     * @group functional
     */
    public function testGetName()
    {
        $client = $this->_getClient();

        $data = $client->request('/_nodes')->getData();
        $rawNodes = $data['nodes'];

        $nodes = $client->getCluster()->getNodes();

        foreach ($nodes as $node) {
            $this->assertEquals($rawNodes[$node->getId()]['name'], $node->getInfo()->getName());
        }
    }
}
