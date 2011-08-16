<?php
require_once dirname(__FILE__) . '/../../../bootstrap.php';

class Elastica_Query_RangeTest extends PHPUnit_Framework_TestCase
{
	public function setUp() {
	}

	public function tearDown() {
	}

	public function testToArray() {

		$range = new Elastica_Query_Range();

		$field = array('from' => 20, 'to' => 40);
		$range->addField('age', $field);

		$expectedArray = array(
			'range' => array(
				'age' => $field,
			)
		);

		$this->assertEquals($expectedArray, $range->toArray());
	}
}
