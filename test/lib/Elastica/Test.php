<?php
require_once dirname(__FILE__) . '/../../bootstrap.php';

class Elastica_Test extends PHPUnit_Framework_TestCase
{
	public function setUp() {

	}
	
	protected function _getClient() {
		return new Elastica_Client();
	}
	
	/**
	 * @param string $name Index name
	 * @return Elastica_Index
	 */
	public function _createIndex($name = 'test') {
		
		$client = $this->_getClient();
		$index = $client->getIndex('elastica_' . $name);
		$index->create(array('index' => array('number_of_shards' => 1, 'number_of_replicas' => 0)), true);
		return $index;
	}
}