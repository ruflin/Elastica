<?php

namespace Elastica\Test\Aggregation;


use Elastica\Aggregation\Min;
use Elastica\Aggregation\Nested;
use Elastica\Document;
use Elastica\Query;
use Elastica\Type\Mapping;

class NestedTest extends BaseAggregationTest
{
    protected function setUp()
    {
        parent::setUp();
        $this->_index = $this->_createIndex("nested");
        $mapping = new Mapping();
        $mapping->setProperties(array(
            "resellers" => array(
                "type" => "nested",
                "properties" => array(
                    "name" => array("type" => "string"),
                    "price" => array("type" => "double")
                )
            )
        ));
        $type = $this->_index->getType("test");
        $type->setMapping($mapping);
        $docs = array(
            new Document("1", array(
                "resellers" => array(
                    "name" => "spacely sprockets",
                    "price" => 5.55
                )
            )),
            new Document("1", array(
                "resellers" => array(
                    "name" => "cogswell cogs",
                    "price" => 4.98
                )
            ))
        );
        $type->addDocuments($docs);
        $this->_index->refresh();
    }

    public function testNestedAggregation()
    {
        $agg = new Nested("resellers", "resellers");
        $min = new Min("min_price");
        $min->setField("price");
        $agg->addAggregation($min);

        $query = new Query();
        $query->addAggregation($agg);
        $results = $this->_index->search($query)->getAggregation("resellers");

        $this->assertEquals(4.98, $results['min_price']['value']);
    }
}
 