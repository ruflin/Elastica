<?php
require_once dirname(__FILE__) . '/../../../bootstrap.php';

class Elastica_Query_BoolTest extends PHPUnit_Framework_TestCase
{
	public function setUp() {
	}

	public function tearDown() {
	}

	public function testSearch() {
		$client = new Elastica_Client();
		$index = new Elastica_Index($client, 'test');
		$index->create(array(), true);

		$type = new Elastica_Type($index, 'helloworld');

		$doc = new Elastica_Document(1, array('id' => 1, 'email' => 'hans@test.com', 'username' => 'hans', 'test' => array('2', '3', '5')));
		$type->addDocument($doc);
		$doc = new Elastica_Document(2, array('id' => 2, 'email' => 'emil@test.com', 'username' => 'emil', 'test' => array('1', '3', '6')));
		$type->addDocument($doc);
		$doc = new Elastica_Document(3, array('id' => 3, 'email' => 'ruth@test.com', 'username' => 'ruth', 'test' => array('2', '3', '7')));
		$type->addDocument($doc);

		// Refresh index
		$index->refresh();

		$boolQuery = new Elastica_Query_Bool();
		$termQuery1 = new Elastica_Query_Term(array('test' => '2'));
		$boolQuery->addMust($termQuery1);
		$resultSet = $type->search($boolQuery);

		$this->assertEquals(2, $resultSet->count());

		$termQuery2 = new Elastica_Query_Term(array('test' => '5'));
		$boolQuery->addMust($termQuery2);
		$resultSet = $type->search($boolQuery);

		$this->assertEquals(1, $resultSet->count());

		$termQuery3 = new Elastica_Query_Term(array('username' => 'hans'));
		$boolQuery->addMust($termQuery3);
		$resultSet = $type->search($boolQuery);

		$this->assertEquals(1, $resultSet->count());

		$termQuery4 = new Elastica_Query_Term(array('username' => 'emil'));
		$boolQuery->addMust($termQuery4);
		$resultSet = $type->search($boolQuery);

		$this->assertEquals(0, $resultSet->count());
	}
}
