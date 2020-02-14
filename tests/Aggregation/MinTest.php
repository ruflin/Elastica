<?php

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\Min;
use Elastica\Document;
use Elastica\Index;
use Elastica\Query;

/**
 * @internal
 */
class MinTest extends BaseAggregationTest
{
    private const MIN_PRICE = 1;

    /**
     * @group functional
     */
    public function testMinAggregation(): void
    {
        $agg = new Min('min_price');
        $agg->setField('price');

        $query = new Query();
        $query->addAggregation($agg);
        $results = $this->_getIndexForTest()->search($query)->getAggregation('min_price');

        $this->assertEquals(self::MIN_PRICE, $results['value']);
    }

    protected function _getIndexForTest(): Index
    {
        $index = $this->_createIndex();

        $index->addDocuments([
            new Document(1, ['price' => 5]),
            new Document(2, ['price' => 8]),
            new Document(3, ['price' => self::MIN_PRICE]),
            new Document(4, ['price' => 3]),
        ]);

        $index->refresh();

        return $index;
    }
}
