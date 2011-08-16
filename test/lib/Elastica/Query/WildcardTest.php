<?php
require_once dirname(__FILE__) . '/../../../bootstrap.php';

class Elastica_Query_WildcardTest extends PHPUnit_Framework_TestCase
{
	public function setUp() {
	}

	public function tearDown() {
	}

	public function testToArray() {
		$key = 'name';
		$value = 'Ru*lin';
		$boost = 2.0;

		$wildcard = new Elastica_Query_Wildcard($key, $value, $boost);

		$expectedArray = array(
			'wildcard' => array(
				$key => array(
					'value' => $value,
					'boost' => $boost
				)
			)
		);

		$this->assertEquals($expectedArray, $wildcard->toArray());
	}
}
