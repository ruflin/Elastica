<?php

namespace Elastica\Test\Aggregation;


use Elastica\Aggregation\Min;
use Elastica\Document;
use Elastica\Query;

class MinTest extends BaseAggregationTest
{
    protected function setUp()
    {
        parent::setUp();
        $this->_index = $this->_createIndex('min');
        $docs = array(
            new Document('1', array('price' => 5)),
            new Document('2', array('price' => 8)),
            new Document('3', array('price' => 1)),
            new Document('4', array('price' => 3)),
        );
        $this->_index->getType('test')->addDocuments($docs);
        $this->_index->refresh();
    }

    public function testMinAggregation()
    {
        $agg = new Min("min_price");
        $agg->setField("price");

        $query = new Query();
        $query->addAggregation($agg);
        $results = $this->_index->search($query)->getAggregation("min_price");

        $this->assertEquals(1, $results['value']);
    }
}
 