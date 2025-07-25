<?php

declare(strict_types=1);

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\Min;
use Elastica\Document;
use Elastica\Index;
use Elastica\Query;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class MinTest extends BaseAggregationTestCase
{
    private const MIN_PRICE = 1;

    #[Group('functional')]
    public function testMinAggregation(): void
    {
        $agg = new Min('min_price');
        $agg->setField('price');

        $query = new Query();
        $query->addAggregation($agg);
        $results = $this->_getIndexForTest()->search($query)->getAggregation('min_price');

        $this->assertEquals(self::MIN_PRICE, $results['value']);
    }

    #[Group('functional')]
    public function testMinAggregationWithMissing(): void
    {
        // feature is buggy on version prior 7.5;
        $this->_checkVersion('7.5');

        $agg = new Min('min_price');
        $agg->setField('price');
        $agg->setMissing(-42);

        $query = new Query();
        $query->addAggregation($agg);
        $results = $this->_getIndexForTest()->search($query)->getAggregation('min_price');

        $this->assertEquals(-42, $results['value']);
    }

    protected function _getIndexForTest(): Index
    {
        $index = $this->_createIndex();

        $index->addDocuments([
            new Document('1', ['price' => 5]),
            new Document('2', ['price' => 8]),
            new Document('3', ['price' => self::MIN_PRICE]),
            new Document('4', ['price' => 3]),
            new Document('5', ['anything' => 'anything']),
        ]);

        $index->refresh();

        return $index;
    }
}
