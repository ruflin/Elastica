<?php

declare(strict_types=1);

namespace Elastica\Test\QueryBuilder\DSL;

use Elastica\Aggregation;
use Elastica\Query\Exists;
use Elastica\QueryBuilder\DSL;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('unit')]
class AggregationTest extends AbstractDSLTestCase
{
    public function testType(): void
    {
        $aggregationDSL = new DSL\Aggregation();

        $this->assertEquals(DSL::TYPE_AGGREGATION, $aggregationDSL->getType());
    }

    public function testInterface(): void
    {
        $aggregationDSL = new DSL\Aggregation();

        $this->_assertImplemented($aggregationDSL, 'avg', Aggregation\Avg::class, ['name']);
        $this->_assertImplemented($aggregationDSL, 'avg_bucket', Aggregation\AvgBucket::class, ['name', 'buckets_path']);
        $this->_assertImplemented($aggregationDSL, 'cardinality', Aggregation\Cardinality::class, ['name']);
        $this->_assertImplemented($aggregationDSL, 'cumulative_sum', Aggregation\CumulativeSum::class, ['name', 'buckets_path']);
        $this->_assertImplemented($aggregationDSL, 'date_histogram', Aggregation\DateHistogram::class, ['name', 'field', 1]);
        $this->_assertImplemented($aggregationDSL, 'date_range', Aggregation\DateRange::class, ['name']);
        $this->_assertImplemented($aggregationDSL, 'derivative', Aggregation\Derivative::class, ['name', 'buckets_path']);
        $this->_assertImplemented($aggregationDSL, 'extended_stats', Aggregation\ExtendedStats::class, ['name']);
        $this->_assertImplemented($aggregationDSL, 'filter', Aggregation\Filter::class, ['name', new Exists('field')]);
        $this->_assertImplemented($aggregationDSL, 'filters', Aggregation\Filters::class, ['name']);
        $this->_assertImplemented($aggregationDSL, 'geo_distance', Aggregation\GeoDistance::class, ['name', 'field', 'origin']);
        $this->_assertImplemented($aggregationDSL, 'geohash_grid', Aggregation\GeohashGrid::class, ['name', 'field']);
        $this->_assertImplemented($aggregationDSL, 'geotile_grid', Aggregation\GeotileGridAggregation::class, ['name', 'field']);
        $this->_assertImplemented($aggregationDSL, 'global', Aggregation\GlobalAggregation::class, ['name']);
        $this->_assertImplemented($aggregationDSL, 'histogram', Aggregation\Histogram::class, ['name', 'field', 1]);
        $this->_assertImplemented($aggregationDSL, 'ipv4_range', Aggregation\IpRange::class, ['name', 'field']);
        $this->_assertImplemented($aggregationDSL, 'max', Aggregation\Max::class, ['name']);
        $this->_assertImplemented($aggregationDSL, 'min', Aggregation\Min::class, ['name']);
        $this->_assertImplemented($aggregationDSL, 'missing', Aggregation\Missing::class, ['name', 'field']);
        $this->_assertImplemented($aggregationDSL, 'nested', Aggregation\Nested::class, ['name', 'path']);
        $this->_assertImplemented($aggregationDSL, 'normalize', Aggregation\NormalizeAggregation::class, ['name', 'buckets_path', 'method']);
        $this->_assertImplemented($aggregationDSL, 'percentiles', Aggregation\Percentiles::class, ['name']);
        $this->_assertImplemented($aggregationDSL, 'percentiles_bucket', Aggregation\PercentilesBucket::class, ['name', 'buckets_path']);
        $this->_assertImplemented($aggregationDSL, 'range', Aggregation\Range::class, ['name']);
        $this->_assertImplemented($aggregationDSL, 'reverse_nested', Aggregation\ReverseNested::class, ['name']);
        $this->_assertImplemented($aggregationDSL, 'scripted_metric', Aggregation\ScriptedMetric::class, ['name', null, null, 'return state.durations', 'return states']);
        $this->_assertImplemented($aggregationDSL, 'significant_terms', Aggregation\SignificantTerms::class, ['name']);
        $this->_assertImplemented($aggregationDSL, 'stats', Aggregation\Stats::class, ['name']);
        $this->_assertImplemented($aggregationDSL, 'stats_bucket', Aggregation\StatsBucket::class, ['name', 'buckets_path']);
        $this->_assertImplemented($aggregationDSL, 'sum', Aggregation\Sum::class, ['name']);
        $this->_assertImplemented($aggregationDSL, 'sum_bucket', Aggregation\SumBucket::class, ['name', 'buckets_path']);
        $this->_assertImplemented($aggregationDSL, 'terms', Aggregation\Terms::class, ['name']);
        $this->_assertImplemented($aggregationDSL, 'top_hits', Aggregation\TopHits::class, ['name']);
        $this->_assertImplemented($aggregationDSL, 'value_count', Aggregation\ValueCount::class, ['name', 'field']);
        $this->_assertImplemented($aggregationDSL, 'bucket_script', Aggregation\BucketScript::class, ['name', [], 'script']);
        $this->_assertImplemented($aggregationDSL, 'serial_diff', Aggregation\SerialDiff::class, ['name', 'buckets_path']);
        $this->_assertImplemented($aggregationDSL, 'adjacency_matrix', Aggregation\AdjacencyMatrix::class, ['name']);
        $this->_assertImplemented($aggregationDSL, 'sampler', Aggregation\Sampler::class, ['name']);
        $this->_assertImplemented($aggregationDSL, 'diversified_sampler', Aggregation\DiversifiedSampler::class, ['name']);
        $this->_assertImplemented($aggregationDSL, 'weighted_avg', Aggregation\WeightedAvg::class, ['name']);
    }
}
