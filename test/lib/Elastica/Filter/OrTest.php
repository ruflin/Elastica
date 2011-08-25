<?php

require_once dirname(__FILE__) . '/../../../bootstrap.php';


class Elastica_Filter_OrTest extends PHPUnit_Framework_TestCase
{
	public function setUp() {
	}

	public function tearDown() {
	}

	public function testAddFilter() {
		$filter = $this->getMockForAbstractClass('Elastica_Filter_Abstract');
		$orFilter = new Elastica_Filter_Or();
		$returnValue = $orFilter->addFilter($filter);
		$this->assertInstanceOf('Elastica_Filter_Or', $returnValue);
	}
	
	public function testToArray() {
		$orFilter = new Elastica_Filter_Or();
		
		$filter1 = new Elastica_Filter_Ids();
		$filter1->setIds('1');
		
		$filter2 = new Elastica_Filter_Ids();
		$filter2->setIds('2');
				
		$orFilter->addFilter($filter1);
		$orFilter->addFilter($filter2);
		
		
		$expectedArray = array(
			'or' => array(
					$filter1->toArray(),
					$filter2->toArray()
				)
			);
			
		$this->assertEquals($expectedArray, $orFilter->toArray());		
	}

}
