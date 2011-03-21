<?php
require_once dirname(__FILE__) . '/../../../bootstrap.php';

class Elastica_Node_InfoTest extends PHPUnit_Framework_TestCase
{
	public function setUp() { }

	public function tearDown() { }

	public function testGet() {
		$client = new Elastica_Client();
		$names = $client->getCluster()->getNodeNames();
		$name = reset($names);

		$node = new Elastica_Node($name, $client);
		$info = new Elastica_Node_Info($node);

		$this->assertInternalType('string', $info->get('os', 'mem', 'total'));
		$this->assertInternalType('array', $info->get('os', 'mem'));
		$this->assertNull($info->get('test', 'notest', 'notexist'));
	}
}