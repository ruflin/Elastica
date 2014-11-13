<?php

namespace Elastica\Test\Aggregation;


use Elastica\Aggregation\GeoDistance;
use Elastica\Document;
use Elastica\Query;
use Elastica\Type\Mapping;

class GeoDistanceTest extends BaseAggregationTest
{
    protected function setUp()
    {
        parent::setUp();
        $this->_index = $this->_createIndex("geo_distance");
        $mapping = new Mapping();
        $mapping->setProperties(array(
            "location" => array("type" => "geo_point")
        ));
        $type = $this->_index->getType("test");
        $type->setMapping($mapping);
        $docs = array(
            new Document("1", array("location" => array("lat" => 32.849437, "lon" => -117.271732))),
            new Document("2", array("location" => array("lat" => 32.798320, "lon" => -117.246648))),
            new Document("3", array("location" => array("lat" => 37.782439, "lon" => -122.392560))),
        );
        $type->addDocuments($docs);
        $this->_index->refresh();
    }

    public function testGeoDistanceAggregation()
    {
        $agg = new GeoDistance("geo", "location", array("lat" => 32.804654, "lon" => -117.242594));
        $agg->addRange(null, 100);
        $agg->setUnit("mi");

        $query = new Query();
        $query->addAggregation($agg);
        $results = $this->_index->search($query)->getAggregation("geo");

        $this->assertEquals(2, $results['buckets'][0]['doc_count']);
    }
}
 