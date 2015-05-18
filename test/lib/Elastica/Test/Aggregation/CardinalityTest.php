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
        $this->_index = $this->_createIndex();
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

    /**
     * @dataProvider invalidPrecisionThresholdProvider
     * @expectedException \InvalidArgumentException
     * @param $threshold
     */
    public function testInvalidPrecisionThreshold($threshold)
    {
        $agg = new Cardinality('threshold');
        $agg->setPrecisionThreshold($threshold);
    }

    /**
     * @dataProvider validPrecisionThresholdProvider
     * @param $threshold
     */
    public function testPrecisionThreshold($threshold)
    {
        $agg = new Cardinality('threshold');
        $agg->setPrecisionThreshold($threshold);

        $this->assertNotNull($agg->getParam('precision_threshold'));
        $this->assertInternalType('int', $agg->getParam('precision_threshold'));
    }

    public function invalidPrecisionThresholdProvider()
    {
        return array(
            'string' => array('100'),
            'float' => array(7.8),
            'boolean' => array(true),
            'array' => array(array()),
            'object' => array(new \StdClass),
        );
    }

    public function validPrecisionThresholdProvider()
    {
        return array(
            'negative-int' => array(-140),
            'zero' => array(0),
            'positive-int' => array(150),
            'more-than-max' => array(40001),
        );
    }
}
