<?php

declare(strict_types=1);

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\ExtendedStatsBucket;
use Elastica\Aggregation\GapPolicyInterface;
use Elastica\Aggregation\Histogram;
use Elastica\Aggregation\Max;
use Elastica\Document;
use Elastica\Index;
use Elastica\Query;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class ExtendedStatsBucketTest extends BaseAggregationTestCase
{
    #[Group('functional')]
    public function testExtendedStatBucketAggregation(): void
    {
        $bucketScriptAggregation = new ExtendedStatsBucket('result', 'age_groups>max_weight');

        $histogramAggregation = new Histogram('age_groups', 'age', 10);

        $histogramAggregation->addAggregation((new Max('max_weight'))->setField('weight'));

        $query = Query::create([])
            ->addAggregation($histogramAggregation)
            ->addAggregation($bucketScriptAggregation)
        ;

        $results = $this->_getIndexForTest()->search($query)->getAggregation('result');

        $this->assertEquals(3, $results['count']);
        $this->assertEquals(50, $results['min']);
        $this->assertEquals(70, $results['max']);
        $this->assertEquals(60, $results['avg']);
        $this->assertEquals(180, $results['sum']);
        $this->assertEquals(11000, $results['sum_of_squares']);
        $this->assertEquals(66.66666666666667, $results['variance']);
        $this->assertEquals(8.16496580927726, $results['std_deviation']);
        $this->assertEquals([
            'upper' => 76.32993161855453,
            'lower' => 43.670068381445475,
            'upper_sampling' => 80.0,
            'lower_sampling' => 40.0,
            'upper_population' => 76.32993161855453,
            'lower_population' => 43.670068381445475,
        ], $results['std_deviation_bounds']);
    }

    #[Group('unit')]
    public function testOverrideBucketsPathThroughSetters(): void
    {
        $serialDiffAgg = new ExtendedStatsBucket('bucket_part', 'foobar');

        $serialDiffAgg
            ->setBucketsPath('age_groups>max_weight')
            ->setFormat('test_format')
            ->setGapPolicy(GapPolicyInterface::INSERT_ZEROS)
        ;

        $expected = [
            'extended_stats_bucket' => [
                'buckets_path' => 'age_groups>max_weight',
                'format' => 'test_format',
                'gap_policy' => GapPolicyInterface::INSERT_ZEROS,
            ],
        ];

        $this->assertEquals($expected, $serialDiffAgg->toArray());
    }

    private function _getIndexForTest(): Index
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
}
