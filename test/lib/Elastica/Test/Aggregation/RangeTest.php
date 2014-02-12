<?php

namespace Elastica\Test\Aggregation;


use Elastica\Aggregation\Range;
use Elastica\Document;
use Elastica\Query;

class RangeTest extends BaseAggregationTest
{
    protected function setUp()
    {
        parent::setUp();
        $this->_index = $this->_createIndex('range');
        $docs = array(
            new Document('1', array('price' => 5)),
            new Document('2', array('price' => 8)),
            new Document('3', array('price' => 1)),
            new Document('4', array('price' => 3)),
            new Document('5', array('price' => 1.5)),
            new Document('6', array('price' => 2)),
        );
        $this->_index->getType('test')->addDocuments($docs);
        $this->_index->refresh();
    }

    public function testRangeAggregation()
    {
        $agg = new Range("range");
        $agg->setField("price");
        $agg->addRange(1.5, 5);

        $query = new Query();
        $query->addAggregation($agg);
        $results = $this->_index->search($query)->getAggregation("range");

        $this->assertEquals(2, $results['buckets'][0]['doc_count']);
    }
}
 