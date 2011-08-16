<?php

require_once dirname(__FILE__) . '/../../../bootstrap.php';


class Elastica_Filter_NotTest extends PHPUnit_Framework_TestCase
{
	public function setUp() {
	}

	public function tearDown() {
	}

	public function testToArray() {
		$idsFilter = new Elastica_Filter_Ids();
		$idsFilter->setIds(12);
		$filter = new Elastica_Filter_Not($idsFilter);

		$expectedArray = array(
			'not' => array(
				'filter' => $idsFilter->toArray()
			)
		);

		$this->assertEquals($expectedArray, $filter->toArray());
	}
}
