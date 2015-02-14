<?php

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\Sum;
use Elastica\Document;
use Elastica\Query;

class SumTest extends BaseAggregationTest
{
    protected function setUp()
    {
        parent::setUp();
        $this->_index = $this->_createIndex();
        $docs = array(
            new Document('1', array('price' => 5)),
            new Document('2', array('price' => 8)),
            new Document('3', array('price' => 1)),
            new Document('4', array('price' => 3)),
        );
        $this->_index->getType('test')->addDocuments($docs);
        $this->_index->refresh();
    }

    public function testSumAggregation()
    {
        $agg = new Sum("sum");
        $agg->setField("price");

        $query = new Query();
        $query->addAggregation($agg);
        $results = $this->_index->search($query)->getAggregation("sum");

        $this->assertEquals(5 + 8 + 1 + 3, $results['value']);
    }
}
