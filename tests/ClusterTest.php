<?php

declare(strict_types=1);

namespace Elastica\Test;

use Elastica\Cluster;
use Elastica\Node;
use Elastica\Test\Base as BaseTest;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('functional')]
class ClusterTest extends BaseTest
{
    public function testGetNodeNames(): void
    {
        $client = $this->_getClient();
        $cluster = new Cluster($client);

        $data = $client->nodes()->info()->asArray();
        $rawNodes = $data['nodes'];

        $expectedNodeNames = [];
        foreach ($rawNodes as $rawNode) {
            $expectedNodeNames[] = $rawNode['name'];
        }

        $nodes = $cluster->getNodeNames();
        \sort($expectedNodeNames);
        \sort($nodes);

        $this->assertSame($expectedNodeNames, $nodes);
    }

    public function testGetNodes(): void
    {
        $cluster = $this->_getClient()->getCluster();
        $nodes = $cluster->getNodes();

        $this->assertGreaterThan(0, \count($nodes));
        $this->assertContainsOnlyInstancesOf(Node::class, $nodes);
    }

    public function testGetState(): void
    {
        $cluster = $this->_getClient()->getCluster();
        $state = $cluster->getState();

        $this->assertArrayHasKey('cluster_name', $state);
        $this->assertArrayHasKey('cluster_uuid', $state);
        $this->assertArrayHasKey('state_uuid', $state);
    }

    public function testGetIndexNames(): void
    {
        $cluster = $this->_getClient()->getCluster();

        $index = $this->_createIndex();
        $indexName = $index->getName();

        $index->delete();
        $cluster->refresh();

        // Checks that index does not exist
        $indexNames = $cluster->getIndexNames();
        $this->assertNotContains($indexName, $indexNames);

        $index = $this->_createIndex();
        $cluster->refresh();

        // Now index should exist
        $indexNames = $cluster->getIndexNames();
        $this->assertContains($index->getname(), $indexNames);
    }

    public function testGetHealth(): void
    {
        $health = $this->_getClient()->getCluster()->getHealth();
        $this->assertSame('green', $health->getStatus());
    }

    public function testGetSettings(): void
    {
        $settings = $this->_getClient()->getCluster()->getSettings();
        $this->assertArrayHasKey('persistent', $settings->get());
        $this->assertArrayHasKey('transient', $settings->get());
    }
}
