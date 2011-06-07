<?php
require_once dirname(__FILE__) . '/../../../bootstrap.php';

class Elastica_Query_QueryStringTest extends PHPUnit_Framework_TestCase
{
	public function setUp() {
	}

	public function tearDown() {
	}

	public function testSearch() {

		$client = new Elastica_Client();
		$index = new Elastica_Index($client, 'test');
		$index->create(array(), true);
		$index->getSettings()->setNumberOfReplicas(0);
		//$index->getSettings()->setNumberOfShards(1);

		$type = new Elastica_Type($index, 'helloworld');

		$doc = new Elastica_Document(1, array('email' => 'test@test.com', 'username' => 'hanswurst', 'test' => array('2', '3', '5')));
		$type->addDocument($doc);

		// Refresh index
		$index->refresh();

		$queryString = new Elastica_Query_QueryString('test*');
		$resultSet = $type->search($queryString);

		$this->assertEquals(1, $resultSet->count());
	}
}
