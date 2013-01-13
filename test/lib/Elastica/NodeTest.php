<?php
require_once dirname(__FILE__) . '/../../bootstrap.php';

class Elastica_NodeTest extends Elastica_Test
{

    public function testCreateNode()
    {
        $client = new Elastica_Client();
        $names = $client->getCluster()->getNodeNames();
        $name = reset($names);

        $node = new Elastica_Node($name, $client);
        $this->assertInstanceOf('Elastica_Node', $node);
    }

    public function testGetInfo()
    {
        $client = new Elastica_Client();
        $names = $client->getCluster()->getNodeNames();
        $name = reset($names);

        $node = new Elastica_Node($name, $client);

        $info = $node->getInfo();

        $this->assertInstanceOf('Elastica_Node_Info', $info);
    }

    public function testGetStats()
    {
        $client = new Elastica_Client();
        $names = $client->getCluster()->getNodeNames();
        $name = reset($names);

        $node = new Elastica_Node($name, $client);

        $stats = $node->getStats();

        $this->assertInstanceOf('Elastica_Node_Stats', $stats);
    }

	/**
	 * Shuts one of two nodes down (if two available)
	 */
	public function testShutdown()
    {
        $client = new Elastica_Client();
        $nodes = $client->getCluster()->getNodes();

        $count = count($nodes);
        if ($count < 2) {
            $this->markTestSkipped('At least two nodes have to be running, because 1 node is shutdown');
        }

   		// Store node info of node with port 9200 for later
		foreach ($nodes as $key => $node) {
			if ($node->getInfo()->getPort() == 9200) {
				$info = $node->getInfo();
				unset($nodes[$key]);
			}
		}

		// Select one of the not port 9200 nodes and shut it down
		$node = array_shift($nodes);
		$node->shutdown('2s');

		// Wait until node is shutdown
        sleep(5);

		// Use still existing node
        $client = new Elastica_Client(array('host' => $info->getIp(), 'port' => $info->getPort()));
        $names = $client->getCluster()->getNodeNames();

        // One node less ...
        $this->assertEquals($count - 1, count($names));
    }
}
