<?php

use Elastica\Test\Base as BaseTest;

/**
 * These tests shuts down node/cluster, so can't be executed with rest testsuite
 * Please use `sudo service elasticsearch restart` after every run of these tests.
 */
class ShutdownTest extends BaseTest
{
    /**
     * @group shutdown
     */
    public function testNodeShutdown()
    {
        // Get cluster nodes
        $client = $this->_getClient();
        $cluster = $client->getCluster();
        $nodes = $cluster->getNodes();

        $nodesCount = count($nodes);

        if ($nodesCount < 2) {
            $this->markTestIncomplete('At least two nodes have to be running, because 1 node is shutdown');
        }

        $portFound = false;
        // sayonara, wolverine, we'd never love you
        foreach ($nodes as $node) {
            if ((int) $node->getInfo()->getPort() === 9201) {
                $portFound = true;
                $node->shutdown('1s');
                break;
            }
        }

        if (!$portFound) {
            $this->markTestSkipped('This test was skipped as in the new docker environment all elasticsearch instances run on the same port');
        }

        // Wait until node is shutdown
        sleep(5);

        // Get nodes again
        $client = $this->_getClient();
        $cluster = $client->getCluster();
        $nodes = $cluster->getNodes();

        // Only one left
        $this->assertCount($nodesCount - 1, $nodes);
    }

    /**
     * @group shutdown
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
