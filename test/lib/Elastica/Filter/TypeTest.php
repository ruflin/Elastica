<?php

require_once dirname(__FILE__) . '/../../../bootstrap.php';


class Elastica_Filter_TypeTest extends PHPUnit_Framework_TestCase
{
	public function setUp() {
	}

	public function tearDown() {
	}

	public function testSetType() {
		$typeFilter = new Elastica_Filter_Type();
		$returnValue = $typeFilter->setType('type_name');
		$this->assertInstanceOf('Elastica_Filter_Type', $returnValue);
	}
	
	public function testToArray() {
		$typeFilter = new Elastica_Filter_Type('type_name');
		
		$expectedArray = array(
			'type' => array('value' => 'type_name')
		);

		$this->assertEquals($expectedArray, $typeFilter->toArray());
	}

}
