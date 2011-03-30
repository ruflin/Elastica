<?php
require_once dirname(__FILE__) . '/../../bootstrap.php';


class Elastica_ClusterTest extends PHPUnit_Framework_TestCase
{
	public function setUp() {
	}

	public function tearDown() {
	}

	public function testGetNodeNames() {
		$client = new Elastica_Client();

		$cluster = new Elastica_Cluster($client);

		$names = $cluster->getNodeNames();

		$this->assertInternalType('array', $names);
		$this->assertGreaterThan(0, count($names));
	}

	public function testGetNodes() {
		$client = new Elastica_Client();
		$cluster = $client->getCluster();

		$nodes = $cluster->getNodes();

		foreach($nodes as $node) {
			$this->assertInstanceOf('Elastica_Node', $node);
		}

		$this->assertGreaterThan(0, count($nodes));
	}

	public function testGetState() {
		$client = new Elastica_Client();
		$cluster = $client->getCluster();
		$state = $cluster->getState();
		$this->assertInternalType('array', $state);
	}
}