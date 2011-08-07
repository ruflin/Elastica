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
}
