<?php
require_once dirname(__FILE__) . '/../../../bootstrap.php';

class Elastica_Query_IdsTest extends PHPUnit_Framework_TestCase
{
	protected $_type;

	public function setUp() {
		$client = new Elastica_Client();
		$index = $client->getIndex('test');
		$index->create(array(), true);
		$type = $index->getType('helloworld');

		$doc = new Elastica_Document(1, array('name' => 'hello world'));
		$type->addDocument($doc);
		$doc = new Elastica_Document(2, array('name' => 'nicolas ruflin'));
		$type->addDocument($doc);
		$doc = new Elastica_Document(3, array('name' => 'ruflin'));
		$type->addDocument($doc);

		$index->refresh();

		$this->_type = $type;
	}

	public function tearDown() {
		$client = new Elastica_Client();
		$index = $client->getIndex('test');
		$index->delete();
	}

	public function testSetIdsSearchSingle() {
		$query = new Elastica_Query_Ids();
		$query->setIds(array('1', '2'));

		$resultSet = $this->_type->search($query);

		$this->assertEquals(2, $resultSet->count());
	}

	public function testSetIdsSearchArray() {
		$query = new Elastica_Query_Ids();
		$query->addId('3');

		$resultSet = $this->_type->search($query);

		$this->assertEquals(1, $resultSet->count());
	}

	public function testComboIdsSearchArray() {
		$query = new Elastica_Query_Ids();

		$query->setIds(array('1', '2'));
		$query->addId('3');

		$resultSet = $this->_type->search($query);

		$this->assertEquals(3, $resultSet->count());
	}
}
