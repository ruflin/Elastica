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

    /**
     * @test
     */
    public function nodeShutdown()
    {
        // Get cluster nodes
        $client = $this->_getClient();
        $cluster = $client->getCluster();
        $nodes = $cluster->getNodes();

        if (count($nodes) < 3) {
            $this->markTestIncomplete('At least three nodes have to be running, because 1 node is shutdown');
        }

        // sayonara, wolverine, we'd never love you
        foreach ($nodes as $node) {
            if ($node->getName() === 'Wolverine') {
                $node->shutdown('2s');
                break;
            }
        }

        // Wait until node is shutdown
        sleep(5);

        // Get nodes again
        $client = $this->_getClient();
        $cluster = $client->getCluster();
        $nodes = $cluster->getNodes();

        // Only two left
        $this->assertCount(2, $nodes);
    }

    /**
     * @test
     * @depends nodeShutdown
     * @expectedException \Elastica\Exception\ConnectionException
     */
    public function clusterShutdown()
    {
        // Get cluster nodes
        $client = $this->_getClient();
        $cluster = $client->getCluster();
        $nodes = $cluster->getNodes();

        if (count($nodes) < 2) {
            $this->markTestIncomplete('At least two nodes have to be running, because we shuts down entire cluster');
        }

        $cluster->shutdown('2s');

        sleep(5);

        $client->getStatus();
    }
}
