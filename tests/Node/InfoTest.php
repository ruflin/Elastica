<?php

declare(strict_types=1);

namespace Elastica\Test\Node;

use Elastica\Node;
use Elastica\Node\Info as NodeInfo;
use Elastica\Test\Base as BaseTest;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class InfoTest extends BaseTest
{
    #[Group('functional')]
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

    #[Group('functional')]
    public function testHasPlugin(): void
    {
        $client = $this->_getClient();
        $nodes = $client->getCluster()->getNodes();
        $node = $nodes[0];
        $info = $node->getInfo();

        $this->assertFalse($info->hasPlugin('foo'));

        if (\version_compare($_SERVER['ES_VERSION'], '8.4.0', '>=')) {
            $this->markTestSkipped('The Ingest Attachment plugin is now included in Elasticsearch. https://www.elastic.co/guide/en/elasticsearch/plugins/8.4/ingest-attachment.html');
        } else {
            $this->assertTrue($info->hasPlugin('ingest-attachment'));
        }
    }

    #[Group('functional')]
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

    #[Group('functional')]
    public function testGetName(): void
    {
        $client = $this->_getClient();

        $data = $client->nodes()->stats()->asArray();
        $rawNodes = $data['nodes'];

        $nodes = $client->getCluster()->getNodes();

        foreach ($nodes as $node) {
            $this->assertEquals($rawNodes[$node->getId()]['name'], $node->getInfo()->getName());
        }
    }

    #[Group('functional')]
    public function testParams(): void
    {
        $client = $this->_getClient();

        $info = $client->getCluster()->getNodes()[0]->getInfo();

        $this->assertArrayHasKey('plugins', $info->getData());
        $info->refresh(['jvm']);
        $this->assertArrayNotHasKey('plugins', $info->getData());
    }
}
