<?php

use Elastica\Test\Base as BaseTest;

/**
 * These tests shuts down node/cluster, so can't be executed with rest testsuite
 * Please use `sudo service elasticsearch restart` after every run of these tests
 */
class ShutdownTest extends BaseTest
{
    protected function tearDown()
    {
        // We can't use Elastica\Test\Base::tearDown here,
        // because cluster was shutted down and indices can't be anymore deleted.
        // So, just do nothing
    }

    public function testNodeShutdown()
    {
        // Get cluster nodes
        $client = $this->_getClient();
        $cluster = $client->getCluster();
        $nodes = $cluster->getNodes();

        if (count($nodes) < 2) {
            $this->markTestIncomplete('At least two nodes have to be running, because 1 node is shutdown');
        }

        // sayonara, wolverine, we'd never love you
        foreach ($nodes as $node) {
            if ((int)$node->getInfo()->getPort() === 9201) {
                $node->shutdown('1s');
                break;
            }
        }

        // Wait until node is shutdown
        sleep(5);

        // Get nodes again
        $client = $this->_getClient();
        $cluster = $client->getCluster();
        $nodes = $cluster->getNodes();

        // Only one left
        $this->assertCount(1, $nodes);
    }

    /**
     * @depends testNodeShutdown
     * @expectedException \Elastica\Exception\Connection\HttpException
     */
    public function testClusterShutdown()
    {
        // Get cluster nodes
        $client = $this->_getClient();
        $cluster = $client->getCluster();
        $nodes = $cluster->getNodes();

        // Shutdown cluster
        $cluster->shutdown('1s');

        // Wait...
        sleep(5);

        // Now exception must be thrown
        $client->getStatus();
    }
}
