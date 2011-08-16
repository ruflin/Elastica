<?php
require_once dirname(__FILE__) . '/../../bootstrap.php';

class Elastica_NodeTest extends PHPUnit_Framework_TestCase
{
	public function setUp() { }

	public function tearDown() { }

	public function testCreateNode() {

		$client = new Elastica_Client();
		$names = $client->getCluster()->getNodeNames();
		$name = reset($names);

		$node = new Elastica_Node($name, $client);
		$this->assertInstanceOf('Elastica_Node', $node);
	}

	public function testGetInfo() {
		$client = new Elastica_Client();
		$names = $client->getCluster()->getNodeNames();
		$name = reset($names);

		$node = new Elastica_Node($name, $client);

		$info = $node->getInfo();

		$this->assertInstanceOf('Elastica_Node_Info', $info);
	}

	public function testGetStats() {
		$client = new Elastica_Client();
		$names = $client->getCluster()->getNodeNames();
		$name = reset($names);

		$node = new Elastica_Node($name, $client);

		$stats = $node->getStats();

		$this->assertInstanceOf('Elastica_Node_Stats', $stats);
	}

	public function testShutdown() {
		$client = new Elastica_Client();
		$nodes = $client->getCluster()->getNodes();

		$count = count($nodes);
		if ($count < 2) {
			$this->markTestSkipped('At least two nodes have to be running, because 1 node is shutdown');
		}

		// Stores node info for later
		$info = $nodes[1]->getInfo();
		$nodes[0]->shutdown('2s');

		sleep(5);

		$client = new Elastica_Client(array('host' => $info->getIp(), 'port' => $info->getPort()));
		$names = $client->getCluster()->getNodeNames();

		// One node less ...
		$this->assertEquals($count - 1, count($names));
	}
}