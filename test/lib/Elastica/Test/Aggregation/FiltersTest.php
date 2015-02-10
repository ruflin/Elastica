<?php

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\Avg;
use Elastica\Aggregation\Filter;
use Elastica\Aggregation\Filters;
use Elastica\Document;
use Elastica\Filter\Range;
use Elastica\Filter\Term;
use Elastica\Query;

class FiltersTest extends BaseAggregationTest
{
    protected function setUp()
    {
        parent::setUp();
        $this->_index = $this->_createIndex("filter");
        $docs = array(
            new Document("1", array("price" => 5, "color" => "blue")),
            new Document("2", array("price" => 8, "color" => "blue")),
            new Document("3", array("price" => 1, "color" => "red")),
            new Document("4", array("price" => 3, "color" => "green")),
        );
        $this->_index->getType("test")->addDocuments($docs);
        $this->_index->refresh();
    }

    public function testToArrayUsingNamedFilters()
    {
        $expected = array(
            "filters" => array(
              "filters" => array(
                  "blue" => array(
                      "term" => array("color" => "blue")
                  ),
                  "red" => array(
                      "term" => array("color" => "red")
                  )
              )
            ),
            "aggs" => array(
                "avg_price" => array("avg" => array("field" => "price")),
            ),
        );

        $agg = new Filters("by_color");
        $agg->addFilter(new Term(array('color' => 'blue')), 'blue');
        $agg->addFilter(new Term(array('color' => 'red')), 'red');

        $avg = new Avg("avg_price");
        $avg->setField("price");
        $agg->addAggregation($avg);

        $this->assertEquals($expected, $agg->toArray());
    }

    public function testToArrayUsingAnonymousFilters()
    {
        $expected = array(
            "filters" => array(
                "filters" => array(
                    array(
                        "term" => array("color" => "blue")
                    ),
                    array(
                        "term" => array("color" => "red")
                    )
                )
            ),
            "aggs" => array(
                "avg_price" => array("avg" => array("field" => "price")),
            ),
        );

        $agg = new Filters("by_color");
        $agg->addFilter(new Term(array("color" => "blue")));
        $agg->addFilter(new Term(array("color" => "red")));

        $avg = new Avg("avg_price");
        $avg->setField("price");
        $agg->addAggregation($avg);

        $this->assertEquals($expected, $agg->toArray());
    }

    public function testFilterAggregation()
    {
        $agg = new Filters("by_color");
        $agg->addFilter(new Term(array('color' => 'blue')), 'blue');
        $agg->addFilter(new Term(array('color' => 'red')), 'red');

        $avg = new Avg("avg_price");
        $avg->setField("price");
        $agg->addAggregation($avg);

        $query = new Query();
        $query->addAggregation($agg);

        $results = $this->_index->search($query)->getAggregation("by_color");

        $resultsForBlue = $results["buckets"]["blue"];
        $resultsForRed  = $results["buckets"]["red"];

        $this->assertEquals(2, $resultsForBlue["doc_count"]);
        $this->assertEquals(1, $resultsForRed["doc_count"]);

        $this->assertEquals((5 + 8) / 2, $resultsForBlue["avg_price"]["value"]);
        $this->assertEquals(1, $resultsForRed["avg_price"]["value"]);
    }
}
