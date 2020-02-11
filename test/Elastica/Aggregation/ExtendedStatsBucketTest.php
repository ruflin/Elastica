<?php

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\ExtendedStatsBucket;
use Elastica\Aggregation\Histogram;
use Elastica\Aggregation\Max;
use Elastica\Document;
use Elastica\Index;
use Elastica\Query;

class ExtendedStatsBucketTest extends BaseAggregationTest
{
    protected function _getIndexForTest(): Index
    {
        $index = $this->_createIndex();

        $index->addDocuments([
            Document::create(['weight' => 60, 'height' => 180, 'age' => 25]),
            Document::create(['weight' => 70, 'height' => 156, 'age' => 32]),
            Document::create(['weight' => 50, 'height' => 155, 'age' => 45]),
        ]);

        $index->refresh();

        return $index;
    }

    /**
     * @group functional
     */
    public function testExtendedStatBucketAggregation()
    {
        $bucketScriptAggregation = new ExtendedStatsBucket('result', 'age_groups>max_weight');

        $histogramAggregation = new Histogram('age_groups', 'age', 10);

        $histogramAggregation->addAggregation((new Max('max_weight'))->setField('weight'));

        $query = Query::create([])
            ->addAggregation($histogramAggregation)
            ->addAggregation($bucketScriptAggregation);

        $results = $this->_getIndexForTest()->search($query)->getAggregation('result');

        $this->assertEquals(3, $results['count']);
        $this->assertEquals(50, $results['min']);
        $this->assertEquals(70, $results['max']);
        $this->assertEquals(60, $results['avg']);
        $this->assertEquals(180, $results['sum']);
        $this->assertEquals(11000, $results['sum_of_squares']);
        $this->assertEquals(66.66666666666667, $results['variance']);
        $this->assertEquals(8.16496580927726, $results['std_deviation']);
        $this->assertEquals(['upper' => 76.32993161855453, 'lower' => 43.670068381445475], $results['std_deviation_bounds']);
    }

    /**
     * @group unit
     */
    public function testConstructThroughSetters()
    {
        $serialDiffAgg = new ExtendedStatsBucket('bucket_part');

        $serialDiffAgg
            ->setBucketsPath('age_groups>max_weight')
            ->setFormat('test_format')
            ->setGapPolicy(10);

        $expected = [
            'extended_stats_bucket' => [
                'buckets_path' => 'age_groups>max_weight',
                'format' => 'test_format',
                'gap_policy' => 10,
            ],
        ];

        $this->assertEquals($expected, $serialDiffAgg->toArray());
    }

    /**
     * @group unit
     */
    public function testToArrayInvalidBucketsPath()
    {
        $this->expectException(\Elastica\Exception\InvalidException::class);

        $serialDiffAgg = new ExtendedStatsBucket('bucket_part');
        $serialDiffAgg->toArray();
    }
}
