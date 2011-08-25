<?php

require_once dirname(__FILE__) . '/../../../bootstrap.php';

class Elastica_Filter_PrefixTest extends PHPUnit_Framework_TestCase
{
	public function setUp() {
	}

	public function tearDown() {
	}
	
	public function testToArray() {
		$field = 'name';
		$prefix = 'ruf';
		
		$filter = new Elastica_Filter_Prefix($field, $prefix);
				
		$expectedArray = array(
			'prefix' => array(
				$field => $prefix
			)
		);
		
		$this->assertequals($expectedArray, $filter->toArray());
	}
}
