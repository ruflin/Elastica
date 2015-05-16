<?php

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\Cardinality;
use Elastica\Document;
use Elastica\Query;

class CardinalityTest extends BaseAggregationTest
{
    protected function _getIndexForTest()
    {
        $index = $this->_createIndex();

        $index->getType("test")->addDocuments(array(
            new Document(1, array("color" => "blue")),
            new Document(2, array("color" => "blue")),
            new Document(3, array("color" => "red")),
            new Document(4, array("color" => "green")),
        ));

        $index->refresh();

        return $index;
    }

    /**
     * @group functional
     */
    public function testCardinalityAggregation()
    {
        $agg = new Cardinality("cardinality");
        $agg->setField("color");

        $query = new Query();
        $query->addAggregation($agg);
        $results = $this->_getIndexForTest()->search($query)->getAggregation("cardinality");

        $this->assertEquals(3, $results['value']);
    }
}
