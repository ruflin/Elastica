<?php
require_once dirname(__FILE__) . '/../../bootstrap.php';

class Elastica_Test extends PHPUnit_Framework_TestCase
{
	public function setUp() {

	}
	
	/**
	 * @return Elastica_Index
	 */
	public function createIndex() {
		$client = new Elastica_Client();
		$index = $client->getIndex('elastica_test');
		$index->create(array('index' => array('number_of_shards' => 1, 'number_of_replicas' => 0)), true);
		return $index;
	}
}