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

        $index->getType('test')->addDocuments(array(
            new Document(1, array('color' => 'blue')),
            new Document(2, array('color' => 'blue')),
            new Document(3, array('color' => 'red')),
            new Document(4, array('color' => 'green')),
        ));

        $index->refresh();

        return $index;
    }

    /**
     * @group functional
     */
    public function testCardinalityAggregation()
    {
        $agg = new Cardinality('cardinality');
        $agg->setField('color');

        $query = new Query();
        $query->addAggregation($agg);
        $results = $this->_getIndexForTest()->search($query)->getAggregation('cardinality');

        $this->assertEquals(3, $results['value']);
    }

    /**
     * @dataProvider invalidPrecisionThresholdProvider
     * @expectedException \InvalidArgumentException
     * @group unit
     *
     * @param $threshold
     */
    public function testInvalidPrecisionThreshold($threshold)
    {
        $agg = new Cardinality('threshold');
        $agg->setPrecisionThreshold($threshold);
    }

    /**
     * @dataProvider validPrecisionThresholdProvider
     * @group unit
     *
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
            'object' => array(new \StdClass()),
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

    /**
     * @dataProvider validRehashProvider
     * @group unit
     *
     * @param bool $rehash
     */
    public function testRehash($rehash)
    {
        $agg = new Cardinality('rehash');
        $agg->setRehash($rehash);

        $this->assertNotNull($agg->getParam('rehash'));
        $this->assertInternalType('boolean', $agg->getParam('rehash'));
    }

    /**
     * @dataProvider invalidRehashProvider
     * @expectedException \InvalidArgumentException
     * @group unit
     *
     * @param mixed $rehash
     */
    public function testInvalidRehash($rehash)
    {
        $agg = new Cardinality('rehash');
        $agg->setRehash($rehash);
    }

    public function invalidRehashProvider()
    {
        return array(
            'string' => array('100'),
            'int' => array(100),
            'float' => array(7.8),
            'array' => array(array()),
            'object' => array(new \StdClass()),
        );
    }

    public function validRehashProvider()
    {
        return array(
            'true' => array(true),
            'false' => array(false),
        );
    }
}
