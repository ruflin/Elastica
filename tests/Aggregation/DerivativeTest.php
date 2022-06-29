<?php

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\DateHistogram;
use Elastica\Aggregation\Derivative;
use Elastica\Aggregation\Max;
use Elastica\Document;
use Elastica\Index;
use Elastica\Query;
use Symfony\Bridge\PhpUnit\ExpectDeprecationTrait;

/**
 * @internal
 */
class DerivativeTest extends BaseAggregationTest
{
    use ExpectDeprecationTrait;

    /**
     * @group unit
     * @group legacy
     */
    public function testLegacyConstructWithNoBucketsPath(): void
    {
        $this->expectDeprecation('Since ruflin/elastica 7.1.3: Not passing a 2nd argument to "Elastica\Aggregation\Derivative::__construct()" is deprecated, pass a string instead. It will be removed in 8.0.');

        new Derivative('derivative');
    }

    /**
     * @group unit
     * @group legacy
     */
    public function testLegacyConstructWithNullBucketsPath(): void
    {
        $this->expectDeprecation('Since ruflin/elastica 7.1.3: Passing null as 2nd argument to "Elastica\Aggregation\Derivative::__construct()" is deprecated, pass a string instead. It will be removed in 8.0.');

        new Derivative('derivative', null);
    }

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

        $deriveAgg = new Derivative('derivative_agg', 'max_agg');

        $maxAgg = (new Max('max_agg'))
            ->setField('value')
            ->addAggregation($deriveAgg)
        ;

        $this->assertEquals($expected, $maxAgg->toArray());
    }

    /**
     * @group functional
     */
    public function testMaxAggregation(): void
    {
        $index = $this->getIndexForTest();

        $deriveAgg = new Derivative('derivative_agg', 'max_agg');
        $maxAgg = (new Max('max_agg'))
            ->setField('value')
        ;

        $dateHistogramAgg = (new DateHistogram('histogram_agg', 'date', 'day'))
            ->setFormat('yyyy-MM-dd')
            ->addAggregation($maxAgg)
            ->addAggregation($deriveAgg)
        ;

        $query = (new Query())
            ->addAggregation($dateHistogramAgg)
        ;

        $dateHistogramAggResult = $index->search($query)->getAggregation('histogram_agg')['buckets'];

        $this->assertArrayNotHasKey('derivative_agg', $dateHistogramAggResult[0]);
        $this->assertEquals(1, $dateHistogramAggResult[1]['derivative_agg']['value']);
        $this->assertEquals(0, $dateHistogramAggResult[2]['derivative_agg']['value']);
        $this->assertEquals(2, $dateHistogramAggResult[3]['derivative_agg']['value']);
        $this->assertEquals(-1, $dateHistogramAggResult[4]['derivative_agg']['value']);
    }

    private function getIndexForTest(): Index
    {
        $index = $this->_createIndex();

        $index->addDocuments([
            new Document('1', ['date' => '2018-12-01', 'value' => 1]),
            new Document('2', ['date' => '2018-12-02', 'value' => 2]),
            new Document('3', ['date' => '2018-12-03', 'value' => 2]),
            new Document('4', ['date' => '2018-12-04', 'value' => 4]),
            new Document('5', ['date' => '2018-12-05', 'value' => 3]),
        ], ['refresh' => 'true']);

        return $index;
    }
}
