<?php

require_once dirname(__FILE__) . '/../../../bootstrap.php';


class Elastica_Facet_DateHistogramTest extends PHPUnit_Framework_TestCase
{
	public function setUp() {
	}

	public function tearDown() {

	}

    public function testClassHierarchy() {

        $facet = new Elastica_Facet_DateHistogram('dateHist1');
        $this->assertInstanceOf('Elastica_Facet_Histogram', $facet);
        $this->assertInstanceOf('Elastica_Facet_Abstract', $facet);
        unset($facet);
    }

	public function testTest() {

		$client = new Elastica_Client();
		$index = $client->getIndex('test');
		$index->create(array(), true);
		$type = $index->getType('helloworld');

        $mapping = new Elastica_Type_Mapping($type, array(
                'name' => array('type' => 'string', 'store' => 'no'),
                'dtmPosted' => array('type' => 'date', 'store' => 'no', 'format' => 'yyyy-MM-dd HH:mm:ss')
            ));
        $type->setMapping($mapping);


		$doc = new Elastica_Document(1, array('name' => 'nicolas ruflin', 'dtmPosted' => "2011-06-23 21:53:00"));
		$type->addDocument($doc);
		$doc = new Elastica_Document(2, array('name' => 'raul martinez jr', 'dtmPosted' => "2011-06-23 09:53:00"));
		$type->addDocument($doc);
		$doc = new Elastica_Document(3, array('name' => 'rachelle clemente', 'dtmPosted' => "2011-07-08 08:53:00"));
		$type->addDocument($doc);
        $doc = new Elastica_Document(4, array('name' => 'elastica search', 'dtmPosted' => "2011-07-08 01:53:00"));
        $type->addDocument($doc);



		$facet = new Elastica_Facet_DateHistogram('dateHist1');
        $facet->setInterval("day");
        $facet->setField("dtmPosted");

		$query = new Elastica_Query();
		$query->addFacet($facet);
		$query->setQuery(new Elastica_Query_MatchAll());
		$index->refresh();

		$response = $type->search($query);
		$facets = $response->getFacets();

        $this->assertEquals(4, $response->getTotalHits());
        $this->assertEquals(2, count($facets['dateHist1']['entries']));
	}
}