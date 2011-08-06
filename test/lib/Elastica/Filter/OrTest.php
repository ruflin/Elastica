<?php

require_once dirname(__FILE__) . '/../../../bootstrap.php';


class Elastica_Filter_OrTest extends PHPUnit_Framework_TestCase
{
	public function setUp() {
	}

	public function tearDown() {
	}

	public function testAddFilter()
	{
		$filter = $this->getMockForAbstractClass('Elastica_Filter_Abstract');
		$orFilter = new Elastica_Filter_Or();
		$returnValue = $orFilter->addFilter($filter);
		$this->assertInstanceOf('Elastica_Filter_Or', $returnValue);
	}
}
