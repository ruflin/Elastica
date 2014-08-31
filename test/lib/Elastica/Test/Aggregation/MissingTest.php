<?php

namespace Elastica\Test\Aggregation;


use Elastica\Aggregation\Missing;
use Elastica\Document;
use Elastica\Query;

class MissingTest extends BaseAggregationTest
{
    protected function setUp()
    {
        parent::setUp();
        $this->_index = $this->_createIndex('missing');
        $docs = array(
            new Document('1', array('price' => 5, "color" => "blue")),
            new Document('2', array('price' => 8, "color" => "blue")),
            new Document('3', array('price' => 1)),
            new Document('4', array('price' => 3, "color" => "green")),
        );
        $this->_index->getType('test')->addDocuments($docs);
        $this->_index->refresh();
    }

    public function testMissingAggregation()
    {
        $agg = new Missing("missing", "color");

        $query = new Query();
        $query->addAggregation($agg);
        $results = $this->_index->search($query)->getAggregation("missing");

        $this->assertEquals(1, $results['doc_count']);
    }
}
 