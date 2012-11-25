<?php
require_once dirname(__FILE__) . '/../../bootstrap.php';

class Elastica_ClusterTest extends Elastica_Test
{

    public function testGetNodeNames()
    {
        $client = new Elastica_Client();

        $cluster = new Elastica_Cluster($client);

        $names = $cluster->getNodeNames();

        $this->assertInternalType('array', $names);
        $this->assertGreaterThan(0, count($names));
    }

    public function testGetNodes()
    {
        $client = new Elastica_Client();
        $cluster = $client->getCluster();

        $nodes = $cluster->getNodes();

        foreach ($nodes as $node) {
            $this->assertInstanceOf('Elastica_Node', $node);
        }

        $this->assertGreaterThan(0, count($nodes));
    }

    public function testGetState()
    {
        $client = new Elastica_Client();
        $cluster = $client->getCluster();
        $state = $cluster->getState();
        $this->assertInternalType('array', $state);
    }

    /**
     * @expectedException Elastica_Exception_Client
     */
    public function testShutdown()
    {
        $this->markTestSkipped('This test shuts down the cluster which means the following tests would not work');
        $client = new Elastica_Client();
        $cluster = $client->getCluster();

        $cluster->shutdown('2s');

        sleep(5);

        $client->getStatus();
    }

    public function testGetIndexNames()
    {
        $client = new Elastica_Client();
        $cluster = $client->getCluster();

        $indexName = 'elastica_test999';
        $index = $this->_createIndex($indexName);
        $index->delete();
        $cluster->refresh();

        // Checks that index does not exist
        $indexNames = $cluster->getIndexNames();
        $this->assertNotContains($index->getName(), $indexNames);

        $index = $this->_createIndex($indexName);
        $cluster->refresh();

        // Now index should exist
        $indexNames = $cluster->getIndexNames();
        $this->assertContains($index->getname(), $indexNames);
    }

    public function testGetHealth()
    {
        $client = new Elastica_Client();
        $this->assertInstanceOf('Elastica_Cluster_Health', $client->getCluster()->getHealth());
    }
}
