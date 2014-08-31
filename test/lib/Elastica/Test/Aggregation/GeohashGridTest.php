<?php

namespace Elastica\Test\Aggregation;


use Elastica\Aggregation\GeohashGrid;
use Elastica\Document;
use Elastica\Query;
use Elastica\Type\Mapping;

class GeohashGridTest extends BaseAggregationTest
{
    protected function setUp()
    {
        parent::setUp();
        $this->_index = $this->_createIndex("geohash_grid");
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

    public function testGeohashGridAggregation()
    {
        $agg = new GeohashGrid("hash", "location");
        $agg->setPrecision(3);

        $query = new Query();
        $query->addAggregation($agg);
        $results = $this->_index->search($query)->getAggregation("hash");

        $this->assertEquals(2, $results['buckets'][0]['doc_count']);
        $this->assertEquals(1, $results['buckets'][1]['doc_count']);
    }
}
 