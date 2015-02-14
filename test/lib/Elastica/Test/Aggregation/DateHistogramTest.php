<?php

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\DateHistogram;
use Elastica\Document;
use Elastica\Query;
use Elastica\Type\Mapping;

class DateHistogramTest extends BaseAggregationTest
{
    protected function setUp()
    {
        parent::setUp();
        $this->_index = $this->_createIndex();
        $mapping = new Mapping();
        $mapping->setProperties(array(
            "created" => array("type" => "date"),
        ));
        $type = $this->_index->getType("test");
        $type->setMapping($mapping);
        $docs = array(
            new Document("1", array("created" => 1390962135000)),
            new Document("2", array("created" => 1390965735000)),
            new Document("3", array("created" => 1390954935000)),
        );
        $type->addDocuments($docs);
        $this->_index->refresh();
    }

    public function testDateHistogramAggregation()
    {
        $agg = new DateHistogram("hist", "created", "1h");

        $query = new Query();
        $query->addAggregation($agg);
        $results = $this->_index->search($query)->getAggregation("hist");

        $this->assertEquals(3, sizeof($results['buckets']));
    }
}
