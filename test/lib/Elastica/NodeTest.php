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
}