<?php

declare(strict_types=1);

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\BucketSelector;
use Elastica\Aggregation\DateHistogram;
use Elastica\Aggregation\Max;
use Elastica\Document;
use Elastica\Index;
use Elastica\Query;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class BucketSelectorTest extends BaseAggregationTestCase
{
    #[Group('unit')]
    public function testToArray(): void
    {
        $expected = [
            'max' => [
                'field' => 'value',
            ],
            'aggs' => [
                'selector_agg' => [
                    'bucket_selector' => [
                        'buckets_path' => ['max' => 'max_agg'],
                        'script' => 'params.max > 5',
                    ],
                ],
            ],
        ];

        $maxAgg = new Max('max_agg');
        $maxAgg->setField('value');

        $selectorAgg = new BucketSelector('selector_agg', ['max' => 'max_agg'], 'params.max > 5');
        $maxAgg->addAggregation($selectorAgg);

        $this->assertEquals($expected, $maxAgg->toArray());
    }

    #[Group('functional')]
    public function testMaxAggregation(): void
    {
        $index = $this->_getIndexForTest();

        $dateHistogramAgg = new DateHistogram('histogram_agg', 'date', 'day');
        $dateHistogramAgg->setFormat('yyyy-MM-dd');

        $maxAgg = new Max('max_agg');
        $maxAgg->setField('value');
        $dateHistogramAgg->addAggregation($maxAgg);

        $bucketAgg = new BucketSelector('selector_agg', ['max' => 'max_agg'], 'params.max > 5');
        $dateHistogramAgg->addAggregation($bucketAgg);

        $query = new Query();
        $query->addAggregation($dateHistogramAgg);

        $dateHistogramAggResult = $index->search($query)->getAggregation('histogram_agg')['buckets'];

        $this->assertCount(4, $dateHistogramAggResult);
        $this->assertEquals(6, $dateHistogramAggResult[0]['max_agg']['value']);
        $this->assertEquals(9, $dateHistogramAggResult[1]['max_agg']['value']);
        $this->assertEquals(11, $dateHistogramAggResult[2]['max_agg']['value']);
        $this->assertEquals(7, $dateHistogramAggResult[3]['max_agg']['value']);
    }

    protected function _getIndexForTest(): Index
    {
        $index = $this->_createIndex();

        $index->addDocuments([
            new Document('1', ['date' => '2018-12-01', 'value' => 1]),
            new Document('2', ['date' => '2018-12-02', 'value' => 2]),
            new Document('3', ['date' => '2018-12-03', 'value' => 5]),
            new Document('4', ['date' => '2018-12-04', 'value' => 4]),
            new Document('5', ['date' => '2018-12-05', 'value' => 6]),
            new Document('6', ['date' => '2018-12-06', 'value' => 9]),
            new Document('7', ['date' => '2018-12-07', 'value' => 11]),
            new Document('8', ['date' => '2018-12-08', 'value' => 4]),
            new Document('9', ['date' => '2018-12-09', 'value' => 7]),
            new Document('10', ['date' => '2018-12-10', 'value' => 4]),
        ]);

        $index->refresh();

        return $index;
    }
}
