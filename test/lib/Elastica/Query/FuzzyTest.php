<?php
require_once dirname(__FILE__) . '/../../../bootstrap.php';

class Elastica_Query_FuzzyTest extends PHPUnit_Framework_TestCase
{
	public function setUp() {
	}

	public function tearDown() {
	}

	public function testToArray() {
		$fuzzy = new Elastica_Query_Fuzzy();

		$fuzzy->addField('user', array('value' => 'Nicolas', 'boost' => 1.0));

		$expectedArray = array(
			'fuzzy' => array(
				'user' => array(
					'value' => 'Nicolas',
					'boost' => 1.0
				)
			)
		);

		$this->assertEquals($expectedArray, $fuzzy->toArray());
	}
}
