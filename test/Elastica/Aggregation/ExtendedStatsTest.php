<?php

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\ExtendedStats;
use Elastica\Document;
use Elastica\Query;

class ExtendedStatsTest extends BaseAggregationTest
{
    protected function _getIndexForTest()
    {
        $index = $this->_createIndex();

        $index->getType('_doc')->addDocuments([
            new Document(1, ['price' => 5]),
            new Document(2, ['price' => 8]),
            new Document(3, ['price' => 1]),
            new Document(4, ['price' => 3]),
        ]);

        $index->refresh();

        return $index;
    }

    /**
     * @group functional
     */
    public function testExtendedStatsAggregation()
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
        $this->assertEquals((5 + 8 + 1 + 3), $results['sum']);
        $this->assertArrayHasKey('sum_of_squares', $results);
    }
}
