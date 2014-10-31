<?php

namespace Elastica\Test\Aggregation;


use Elastica\Aggregation\Avg;
use Elastica\Document;
use Elastica\Index;
use Elastica\Query;

class AvgTest extends BaseAggregationTest
{
    protected function setUp()
    {
        parent::setUp();
        $this->_index = $this->_createIndex('avg');
        $docs = array(
            new Document('1', array('price' => 5)),
            new Document('2', array('price' => 8)),
            new Document('3', array('price' => 1)),
            new Document('4', array('price' => 3)),
        );
        $this->_index->getType('test')->addDocuments($docs);
        $this->_index->refresh();
    }

    public function testAvgAggregation()
    {
        $agg = new Avg("avg");
        $agg->setField('price');

        $query = new Query();
        $query->addAggregation($agg);
        $results = $this->_index->search($query)->getAggregations();
        $this->assertEquals((5 + 8 + 1 + 3) / 4.0, $results['avg']['value']);
    }
}
 