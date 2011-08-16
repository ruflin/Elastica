<?php
require_once dirname(__FILE__) . '/../../../bootstrap.php';

class Elastica_Query_IdsTest extends PHPUnit_Framework_TestCase
{
	protected $_index;
	protected $_type;

	public function setUp() {
		$client = new Elastica_Client();
		$index = $client->getIndex('test');
		$index->create(array(), true);

		$type1 = $index->getType('helloworld1');
		$type2 = $index->getType('helloworld2');

		$doc = new Elastica_Document(1, array('name' => 'hello world'));
		$type1->addDocument($doc);

		$doc = new Elastica_Document(2, array('name' => 'nicolas ruflin'));
		$type1->addDocument($doc);

		$doc = new Elastica_Document(3, array('name' => 'ruflin'));
		$type1->addDocument($doc);

		$doc = new Elastica_Document(4, array('name' => 'hello world again'));
		$type2->addDocument($doc);

		$index->refresh();

		$this->_type = $type1;
		$this->_index = $index;
	}

	public function tearDown() {
		$client = new Elastica_Client();
		$index = $client->getIndex('test');
		$index->delete();
	}

	public function testSetIdsSearchSingle() {
		$query = new Elastica_Query_Ids();
		$query->setIds('1');

		$resultSet = $this->_type->search($query);

		$this->assertEquals(1, $resultSet->count());
	}

	public function testSetIdsSearchArray() {
		$query = new Elastica_Query_Ids();
		$query->setIds(array('1', '2'));

		$resultSet = $this->_type->search($query);

		$this->assertEquals(2, $resultSet->count());
	}

	public function testAddIdsSearchSingle() {
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

	public function testSetTypeSingleSearchSingle() {
		$query = new Elastica_Query_Ids();

		$query->setIds('1');
		$query->setType('helloworld1');

		$resultSet = $this->_index->search($query);

		$this->assertEquals(1, $resultSet->count());
	}

	public function testSetTypeSingleSearchArray() {
		$query = new Elastica_Query_Ids();

		$query->setIds(array('1', '2'));
		$query->setType('helloworld1');

		$resultSet = $this->_index->search($query);

		$this->assertEquals(2, $resultSet->count());
	}

	public function testSetTypeSingleSearchSingleDocInOtherType() {
		$query = new Elastica_Query_Ids();

		// Doc 4 is in the second type...
		$query->setIds('4');
		$query->setType('helloworld1');

		$resultSet = $this->_index->search($query);

		// ...therefore 0 results should be returned
		$this->assertEquals(0, $resultSet->count());
	}

	public function testSetTypeSingleSearchArrayDocInOtherType() {
		$query = new Elastica_Query_Ids();

		// Doc 4 is in the second type...
		$query->setIds(array('1', '4'));
		$query->setType('helloworld1');

		$resultSet = $this->_index->search($query);

		// ...therefore only 1 result should be returned
		$this->assertEquals(1, $resultSet->count());
	}

	public function testSetTypeArraySearchArray() {
		$query = new Elastica_Query_Ids();

		$query->setIds(array('1', '4'));
		$query->setType(array('helloworld1', 'helloworld2'));

		$resultSet = $this->_index->search($query);

		$this->assertEquals(2, $resultSet->count());
	}

	public function testSetTypeArraySearchSingle() {
		$query = new Elastica_Query_Ids();

		$query->setIds('4');
		$query->setType(array('helloworld1', 'helloworld2'));

		$resultSet = $this->_index->search($query);

		$this->assertEquals(1, $resultSet->count());
	}
}

