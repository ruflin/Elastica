<?php

namespace Elastica\Test\Aggregation;


use Elastica\Aggregation\Terms;
use Elastica\Document;
use Elastica\Query;

class TermsTest extends BaseAggregationTest
{
    protected function setUp()
    {
        parent::setUp();
        $this->_index = $this->_createIndex("terms");
        $docs = array(
            new Document("1", array("color" => "blue")),
            new Document("2", array("color" => "blue")),
            new Document("3", array("color" => "red")),
            new Document("4", array("color" => "green")),
        );
        $this->_index->getType("test")->addDocuments($docs);
        $this->_index->refresh();
    }

    public function testTermsAggregation()
    {
        $agg = new Terms("terms");
        $agg->setField("color");

        $query = new Query();
        $query->addAggregation($agg);
        $results = $this->_index->search($query)->getAggregation("terms");

        $this->assertEquals(2, $results['buckets'][0]['doc_count']);
        $this->assertEquals("blue", $results['buckets'][0]['key']);
    }
}
 