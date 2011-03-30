<?php

require_once dirname(__FILE__) . '/../../bootstrap.php';

/**
 * Tests the example code
 */
class Elastica_FacetsTest extends PHPUnit_Framework_TestCase
{
	public function setUp() {
	}

	public function tearDown() {
	}

	public function testTest() {
		$this->markTestIncomplete();

		$termFacet = new Elastica_Facet_Terms('test');
		$termFacet->setField('hello');
		$termFacet->setSize(10);
		print_r($termFacet->toArray());

		$facet = new Elastica_Facets();
		$facet->addFacet($termFacet);

		print_r($facet->toArray());



	}
}