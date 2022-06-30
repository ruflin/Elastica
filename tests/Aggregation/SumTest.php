<?php

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\Sum;
use Elastica\Document;
use Elastica\Index;
use Elastica\Query;

/**
 * @internal
 */
class SumTest extends BaseAggregationTest
{
    /**
     * @group functional
     */
    public function testSumAggregation(): void
    {
        $agg = new Sum('sum');
        $agg->setField('price');

        $query = new Query();
        $query->addAggregation($agg);
        $results = $this->_getIndexForTest()->search($query)->getAggregation('sum');

        $this->assertEquals(5 + 8 + 1 + 3, $results['value']);
    }

    /**
     * @group functional
     */
    public function testSumAggregationWithMissing(): void
    {
        $agg = new Sum('sum');
        $agg->setField('price');
        $agg->setMissing(10);

        $query = new Query();
        $query->addAggregation($agg);
        $results = $this->_getIndexForTest()->search($query)->getAggregation('sum');

        $this->assertEquals(5 + 8 + 1 + 3 + 10, $results['value']);
    }

    protected function _getIndexForTest(): Index
    {
        $index = $this->_createIndex();

        $index->addDocuments([
            new Document('1', ['price' => 5]),
            new Document('2', ['price' => 8]),
            new Document('3', ['price' => 1]),
            new Document('4', ['price' => 3]),
            new Document('5', ['anything' => 'anything']),
        ]);

        $index->refresh();

        return $index;
    }
}
