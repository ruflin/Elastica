<?php

declare(strict_types=1);

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\ExtendedStats;
use Elastica\Document;
use Elastica\Index;
use Elastica\Query;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class ExtendedStatsTest extends BaseAggregationTestCase
{
    #[Group('functional')]
    public function testExtendedStatsAggregation(): void
    {
        $agg = new ExtendedStats('stats');
        $agg->setField('price');

        $query = new Query();
        $query->addAggregation($agg);
        $results = $this->_getIndexForTest()->search($query)->getAggregation('stats');

        $this->assertEquals(4, $results['count']);
        $this->assertEquals(1, $results['min']);
        $this->assertEquals(8, $results['max']);
        $this->assertEquals((5 + 8 + 1 + 3) / 4.0, $results['avg']);
        $this->assertEquals(5 + 8 + 1 + 3, $results['sum']);
        $this->assertArrayHasKey('sum_of_squares', $results);
    }

    #[Group('functional')]
    public function testExtendedStatsAggregationWithMissing(): void
    {
        $agg = new ExtendedStats('stats');
        $agg->setField('price');
        $agg->setMissing(10);

        $query = new Query();
        $query->addAggregation($agg);
        $results = $this->_getIndexForTest()->search($query)->getAggregation('stats');

        $this->assertEquals(5, $results['count']);
        $this->assertEquals(1, $results['min']);
        $this->assertEquals(10, $results['max']);
        $this->assertEquals((5 + 8 + 1 + 3 + 10) / 5.0, $results['avg']);
        $this->assertEquals(5 + 8 + 1 + 3 + 10, $results['sum']);
        $this->assertArrayHasKey('sum_of_squares', $results);
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
