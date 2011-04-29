<?php

require_once dirname(__FILE__) . '/../../../bootstrap.php';


class Elastica_Filter_IdsTest extends PHPUnit_Framework_TestCase
{
	public function setUp() {
	}

	public function tearDown() {
	}

	public function testFilterIds() {
		$client = new Elastica_Client();
		$index = $client->getIndex('test');
		$index->create(array(), true);

		$type = $index->getType('test');

		// Add doc 1

		for ($i = 1; $i < 100; $i++) {
			$doc = new Elastica_Document($i, array('name' => 'ruflin'));
			$type->addDocument($doc);
		}

		$index->optimize();
		$index->refresh();

		$ids = array(1, 7, 13);

		// Only one point should be in radius
		$query = new Elastica_Query();
		$idsFilter = new Elastica_Filter_Ids($type, $ids);
		$query->setFilter($idsFilter);

		$result = $type->search($query);

		$this->assertEquals(count($ids), $result->count());

		// add one more id in list
		$idsFilter->addId(39);
		$query->setFilter($idsFilter);
		$result = $type->search($query);

		$this->assertEquals(count($ids) + 1, $result->count());

		// add id not in list
		$idsFilter->addId(104);
		$query->setFilter($idsFilter);
		$result = $type->search($query);

		$this->assertEquals(count($ids) + 1, $result->count());
	}
}
