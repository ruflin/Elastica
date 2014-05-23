<?php

namespace Elastica\Test\Aggregation;


use Elastica\Aggregation\Avg;
use Elastica\Aggregation\Filter;
use Elastica\Document;
use Elastica\Filter\Range;
use Elastica\Filter\Term;
use Elastica\Query;

class FilterTest extends BaseAggregationTest
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

    public function testToArray()
    {
        $expected = array(
            "filter" => array("range" => array("stock" => array("gt" => 0))),
            "aggs" => array(
                "avg_price" => array("avg" => array("field" => "price"))
            )
        );

        $agg = new Filter("in_stock_products");
        $agg->setFilter(new Range("stock", array("gt" => 0)));
        $avg = new Avg("avg_price");
        $avg->setField("price");
        $agg->addAggregation($avg);

        $this->assertEquals($expected, $agg->toArray());
    }

    public function testFilterAggregation()
    {
        $agg = new Filter("filter");
        $agg->setFilter(new Term(array("color" => "blue")));
        $avg = new Avg("price");
        $avg->setField("price");
        $agg->addAggregation($avg);

        $query = new Query();
        $query->addAggregation($agg);

        $results = $this->_index->search($query)->getAggregation("filter");
        $results = $results['price']['value'];

        $this->assertEquals((5 + 8) / 2.0, $results);
    }

    public function testFilterNoSubAggregation()
    {
        $agg = new Avg("price");
        $agg->setField("price");

        $query = new Query();
        $query->addAggregation($agg);

        $results = $this->_index->search($query)->getAggregation("price");
        $results = $results['value'];

        $this->assertEquals((5 + 8 + 1 + 3) / 4.0, $results);
    }
}
 