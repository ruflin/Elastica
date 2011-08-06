<?php

require_once dirname(__FILE__) . '/../../../bootstrap.php';


class Elastica_Filter_RangeTest extends PHPUnit_Framework_TestCase
{
	public function setUp() {
	}

	public function tearDown() {
	}

	public function testAddField()
	{
		$rangeFilter = new Elastica_Filter_Range();
		$returnValue = $rangeFilter->addField('fieldName', array('to' => 'value'));
		$this->assertInstanceOf('Elastica_Filter_Range', $returnValue);
	}
}
