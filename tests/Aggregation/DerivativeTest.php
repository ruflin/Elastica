<?php

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\DateHistogram;
use Elastica\Aggregation\Derivative;
use Elastica\Aggregation\Max;
use Elastica\Document;
use Elastica\Query;

/**
 * @internal
 */
class DerivativeTest extends BaseAggregationTest
{
    /**
     * @group unit
     */
    public function testToArray(): void
    {
        $expected = [
            'max' => [
                'field' => 'value',
            ],
            'aggs' => [
                'derivative_agg' => [
                    'derivative' => [
                        'buckets_path' => 'max_agg',
                    ],
                ],
            ],
        ];

        $maxAgg = new Max('max_agg');
        $maxAgg->setField('value');

        $deriveAgg = new Derivative('derivative_agg', 'max_agg');
        $maxAgg->addAggregation($deriveAgg);

        $this->assertEquals($expected, $maxAgg->toArray());
    }

    /**
     * @group functional
     */
    public function testMaxAggregation(): void
    {
        $index = $this->_getIndexForTest();

        $dateHistogramAgg = new DateHistogram('histogram_agg', 'date', 'day');
        $dateHistogramAgg->setFormat('yyyy-MM-dd');

        $maxAgg = new Max('max_agg');
        $maxAgg->setField('value');
        $dateHistogramAgg->addAggregation($maxAgg);

        $deriveAgg = new Derivative('derivative_agg', 'max_agg');
        $dateHistogramAgg->addAggregation($deriveAgg);

        $query = new Query();
        $query->addAggregation($dateHistogramAgg);

        $dateHistogramAggResult = $index->search($query)->getAggregation('histogram_agg')['buckets'];

        $this->assertArrayNotHasKey('derivative_agg', $dateHistogramAggResult[0]);
        $this->assertEquals(1, $dateHistogramAggResult[1]['derivative_agg']['value']);
        $this->assertEquals(0, $dateHistogramAggResult[2]['derivative_agg']['value']);
        $this->assertEquals(2, $dateHistogramAggResult[3]['derivative_agg']['value']);
        $this->assertEquals(-1, $dateHistogramAggResult[4]['derivative_agg']['value']);
    }

    protected function _getIndexForTest()
    {
        $index = $this->_createIndex();

        $index->addDocuments([
            new Document(1, ['date' => '2018-12-01', 'value' => 1]),
            new Document(2, ['date' => '2018-12-02', 'value' => 2]),
            new Document(3, ['date' => '2018-12-03', 'value' => 2]),
            new Document(4, ['date' => '2018-12-04', 'value' => 4]),
            new Document(5, ['date' => '2018-12-05', 'value' => 3]),
        ]);

        $index->refresh();

        return $index;
    }
}
