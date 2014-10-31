<?php

namespace Elastica\Test\Aggregation;


use Elastica\Aggregation\Cardinality;
use Elastica\Document;
use Elastica\Query;

class CardinalityTest extends BaseAggregationTest
{
    protected function setUp()
    {
        parent::setUp();
        $this->_index = $this->_createIndex("cardinality");
        $docs = array(
            new Document("1", array("color" => "blue")),
            new Document("2", array("color" => "blue")),
            new Document("3", array("color" => "red")),
            new Document("4", array("color" => "green")),
        );
        $this->_index->getType("test")->addDocuments($docs);
        $this->_index->refresh();
    }

    public function testCardinalityAggregation()
    {
        $agg = new Cardinality("cardinality");
        $agg->setField("color");

        $query = new Query();
        $query->addAggregation($agg);
        $results = $this->_index->search($query)->getAggregation("cardinality");

        $this->assertEquals(3, $results['value']);
    }
}
 
