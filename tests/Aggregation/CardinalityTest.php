<?php

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\Cardinality;
use Elastica\Document;
use Elastica\Index;
use Elastica\Mapping;
use Elastica\Query;

/**
 * @internal
 */
class CardinalityTest extends BaseAggregationTest
{
    /**
     * @group functional
     */
    public function testCardinalityAggregation(): void
    {
        $agg = new Cardinality('cardinality');
        $agg->setField('color');

        $query = new Query();
        $query->addAggregation($agg);
        $results = $this->_getIndexForTest()->search($query)->getAggregation('cardinality');

        $this->assertEquals(3, $results['value']);
    }

    public function validPrecisionThresholdProvider()
    {
        return [
            'negative-int' => [-140],
            'zero' => [0],
            'positive-int' => [150],
            'more-than-max' => [40001],
        ];
    }

    /**
     * @dataProvider validPrecisionThresholdProvider
     * @group unit
     */
    public function testPrecisionThreshold(int $threshold): void
    {
        $agg = new Cardinality('threshold');
        $agg->setPrecisionThreshold($threshold);

        $this->assertNotNull($agg->getParam('precision_threshold'));
        $this->assertIsInt($agg->getParam('precision_threshold'));
    }

    /**
     * @dataProvider validRehashProvider
     * @group unit
     *
     * @param bool $rehash
     */
    public function testRehash($rehash): void
    {
        $agg = new Cardinality('rehash');
        $agg->setRehash($rehash);

        $this->assertNotNull($agg->getParam('rehash'));
        $this->assertIsBool($agg->getParam('rehash'));
    }

    public function validRehashProvider()
    {
        return [
            'true' => [true],
            'false' => [false],
        ];
    }

    protected function _getIndexForTest(): Index
    {
        $index = $this->_createIndex();

        $mapping = new Mapping([
            'color' => ['type' => 'keyword'],
        ]);
        $index->setMapping($mapping);

        $index->addDocuments([
            new Document(1, ['color' => 'blue']),
            new Document(2, ['color' => 'blue']),
            new Document(3, ['color' => 'red']),
            new Document(4, ['color' => 'green']),
        ]);

        $index->refresh();

        return $index;
    }
}
