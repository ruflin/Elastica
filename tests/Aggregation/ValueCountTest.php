<?php

declare(strict_types=1);

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\ValueCount;
use Elastica\Document;
use Elastica\Index;
use Elastica\Query;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class ValueCountTest extends BaseAggregationTestCase
{
    #[Group('functional')]
    public function testValueCountAggregation(): void
    {
        $agg = new ValueCount('count', 'price');

        $query = new Query();
        $query->addAggregation($agg);
        $results = $this->_getIndexForTest()->search($query)->getAggregation('count');

        $this->assertEquals(5, $results['value']);
    }

    protected function _getIndexForTest(): Index
    {
        $index = $this->_createIndex();

        $index->addDocuments([
            new Document('1', ['price' => 5]),
            new Document('2', ['price' => 8]),
            new Document('3', ['price' => 1]),
            new Document('4', ['price' => 3]),
            new Document('5', ['price' => 3]),
        ]);

        $index->refresh();

        return $index;
    }
}
