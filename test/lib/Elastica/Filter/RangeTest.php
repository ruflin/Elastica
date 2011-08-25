<?php

require_once dirname(__FILE__) . '/../../../bootstrap.php';


class Elastica_Filter_RangeTest extends PHPUnit_Framework_TestCase
{
	public function setUp() {
	}

	public function tearDown() {
	}

	public function testAddField() {
		$rangeFilter = new Elastica_Filter_Range();
		$returnValue = $rangeFilter->addField('fieldName', array('to' => 'value'));
		$this->assertInstanceOf('Elastica_Filter_Range', $returnValue);
	}
	
	public function testToArray() {
		$filter = new Elastica_Filter_Range();
		
		$fromTo = array('from' => 'ra', 'to' => 'ru');
		$filter->addField('name', $fromTo);
		
		$expectedArray = array(
			'range' => array(
				'name' => $fromTo
			)
		);
				
		$this->assertEquals($expectedArray, $filter->toArray());
	}
}
