<?php

require_once dirname(__FILE__) . '/../../../bootstrap.php';


class Elastica_Filter_AndTest extends PHPUnit_Framework_TestCase
{
	public function setUp() {
	}

	public function tearDown() {
	}

	public function testToArray() {
		$and = new Elastica_Filter_And();
		$this->assertEquals(array('and' => array()), $and->toArray());

		$idsFilter = new Elastica_Filter_Ids();
		$idsFilter->setIds(12);

		$and->addFilter($idsFilter);
		$and->addFilter($idsFilter);

		$expectedArray = array(
			'and' => array(
				$idsFilter->toArray(),
				$idsFilter->toArray()
			)
		);

		$this->assertEquals($expectedArray, $and->toArray());
	}


	public function testSetCache() {

		$client = new Elastica_Client();
		$index = $client->getIndex('test');
		$index->create(array(), true);
		$type = $index->getType('test');

		$doc = new Elastica_Document(1, array('name' => 'hello world'));
		$type->addDocument($doc);
		$doc = new Elastica_Document(2, array('name' => 'nicolas ruflin'));
		$type->addDocument($doc);
		$doc = new Elastica_Document(3, array('name' => 'ruflin'));
		$type->addDocument($doc);


		$and = new Elastica_Filter_And();

		$idsFilter1 = new Elastica_Filter_Ids();
		$idsFilter1->setIds(1);

		$idsFilter2 = new Elastica_Filter_Ids();
		$idsFilter2->setIds(1);

		$and->addFilter($idsFilter1);
		$and->addFilter($idsFilter2);

		$index->refresh();
		$and->setCached(true);



		$resultSet = $type->search($and);

		$this->assertEquals(1, $resultSet->count());
	}
}
