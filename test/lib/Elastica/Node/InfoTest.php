<?php

require_once dirname(__FILE__) . '/../../../bootstrap.php';

class Elastica_Node_InfoTest extends Elastica_Test
{
	public function setUp() { }

	public function tearDown() { }

	public function testGet() {
		$client = new Elastica_Client();
		$names = $client->getCluster()->getNodeNames();
		$name = reset($names);

		$node = new Elastica_Node($name, $client);
		$info = new Elastica_Node_Info($node);

		$this->assertNull($info->get('os', 'mem', 'total'));

		// Load os infos
		$info = new Elastica_Node_Info($node, array('os'));

		$this->assertInternalType('string', $info->get('os', 'mem', 'total'));
		$this->assertInternalType('array', $info->get('os', 'mem'));
		$this->assertNull($info->get('test', 'notest', 'notexist'));
	}
}