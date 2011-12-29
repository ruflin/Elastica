<?php
require_once dirname(__FILE__) . '/../../../bootstrap.php';

class Elastica_Query_FilteredTest extends PHPUnit_Framework_TestCase
{
	public function setUp() {
	}

	public function tearDown() {
	}

	public function testFilteredSearch() {
		$client = new Elastica_Client();
		$index = $client->getIndex('test');
		$index->create(array(), true);
		$type = $index->getType('helloworld');

		$doc = new Elastica_Document(1, array('id' => 1, 'email' => 'test@test.com', 'username' => 'hanswurst', 'test' => array('2', '3', '5')));
		$type->addDocument($doc);
		$doc = new Elastica_Document(2, array('id' => 2, 'email' => 'test@test.com', 'username' => 'peter', 'test' => array('2', '3', '5')));
		$type->addDocument($doc);

		$queryString = new Elastica_Query_QueryString('test*');

		$filter1 = new Elastica_Filter_Term();
		$filter1->setTerm('username', 'peter');

		$filter2 = new Elastica_Filter_Term();
		$filter2->setTerm('username', 'qwerqwer');

		$query1 = new Elastica_Query_Filtered($queryString, $filter1);
		$query2 = new Elastica_Query_Filtered($queryString, $filter2);
		$index->refresh();

		$resultSet = $type->search($queryString);
		$this->assertEquals(2, $resultSet->count());

		$resultSet = $type->search($query1);
		$this->assertEquals(1, $resultSet->count());

		$resultSet = $type->search($query2);
		$this->assertEquals(0, $resultSet->count());
	}
}
