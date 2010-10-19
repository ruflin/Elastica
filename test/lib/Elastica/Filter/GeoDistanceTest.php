<?php

require_once dirname(__FILE__) . '/../../../bootstrap.php';


class ElasticSearch_Filter_GeoDistanceTest extends PHPUnit_Framework_TestCase
{
    public function setUp() {   
    }
    
    public function tearDown() {
    }
    
    public function testGeoPoint() {
    	$client = new ElasticSearch_Client();
    	$index = $client->getIndex('test');
		$index->create(array(), true);

		$type = $index->getType('test');
		
		// Set mapping
		$type->setMapping(array('point' => array('type' => 'geo_point')));

    	
		// Add doc 1
    	$doc1 = new ElasticSearch_Document(1, 
			array(
				'name' => 'ruflin',
			)
		);
		
		$doc1->addGeoPoint('point', 17, 19);    	
    	$type->addDocument($doc1);

		// Add doc 2
    	$doc2 = new ElasticSearch_Document(2, 
			array(
				'name' => 'ruflin',
			)
		);
		
		$doc2->addGeoPoint('point', 30, 40);    	
    	$type->addDocument($doc2);


    	$index->optimize();
		sleep(1);
		
		// Only one point should be in radius
		$query = new ElasticSearch_Query();
		$geoFilter = new ElasticSearch_Filter_GeoDistance('point', 30, 40, '1km');
		$filter = new ElasticSearch_Filter($geoFilter, new ElasticSearch_Query_MatchAll());
		$query->addFilter($filter);
		$this->assertEquals(1, $type->search($query)->count());
		
		// Both points should be inside
		$query = new ElasticSearch_Query();
		$geoFilter = new ElasticSearch_Filter_GeoDistance('point', 30, 40, '40000km');
		$filter = new ElasticSearch_Filter($geoFilter, new ElasticSearch_Query_MatchAll());
		$query->addFilter($filter);
		$this->assertEquals(2, $type->search($query)->count());
    }
}
