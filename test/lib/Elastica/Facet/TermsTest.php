<?php

require_once dirname(__FILE__) . '/../../../bootstrap.php';


class Elastica_Facet_TermsTest extends PHPUnit_Framework_TestCase
{
	public function setUp() {
	}

	public function tearDown() {

	}

	public function testQuery() {

		$client = new Elastica_Client();
		$index = $client->getIndex('test');
		$index->create(array(), true);
		$type = $index->getType('helloworld');

		$doc = new Elastica_Document(1, array('name' => 'nicolas ruflin'));
		$type->addDocument($doc);
		$doc = new Elastica_Document(2, array('name' => 'ruflin test'));
		$type->addDocument($doc);
		$doc = new Elastica_Document(2, array('name' => 'nicolas helloworld'));
		$type->addDocument($doc);


		$facet = new Elastica_Facet_Terms('test');
		$facet->setField('name');

		$query = new Elastica_Query();
		$query->addFacet($facet);
		$query->setQuery(new Elastica_Query_MatchAll());

		$index->refresh();

		$response = $type->search($query);
		$facets = $response->getFacets();

		$this->assertEquals(3, count($facets['test']['terms']));
	}	
}