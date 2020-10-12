<?php

namespace Elastica\QueryBuilder\DSL;

use Elastica\Aggregation\AdjacencyMatrix;
use Elastica\Aggregation\Avg;
use Elastica\Aggregation\AvgBucket;
use Elastica\Aggregation\BucketScript;
use Elastica\Aggregation\Cardinality;
use Elastica\Aggregation\DateHistogram;
use Elastica\Aggregation\DateRange;
use Elastica\Aggregation\DiversifiedSampler;
use Elastica\Aggregation\ExtendedStats;
use Elastica\Aggregation\Filter;
use Elastica\Aggregation\Filters;
use Elastica\Aggregation\GeoDistance;
use Elastica\Aggregation\GeohashGrid;
use Elastica\Aggregation\GlobalAggregation;
use Elastica\Aggregation\Histogram;
use Elastica\Aggregation\IpRange;
use Elastica\Aggregation\Max;
use Elastica\Aggregation\Min;
use Elastica\Aggregation\Missing;
use Elastica\Aggregation\Nested;
use Elastica\Aggregation\Percentiles;
use Elastica\Aggregation\PercentilesBucket;
use Elastica\Aggregation\Range;
use Elastica\Aggregation\ReverseNested;
use Elastica\Aggregation\Sampler;
use Elastica\Aggregation\ScriptedMetric;
use Elastica\Aggregation\SerialDiff;
use Elastica\Aggregation\SignificantTerms;
use Elastica\Aggregation\Stats;
use Elastica\Aggregation\Sum;
use Elastica\Aggregation\SumBucket;
use Elastica\Aggregation\Terms;
use Elastica\Aggregation\TopHits;
use Elastica\Aggregation\ValueCount;
use Elastica\Exception\NotImplementedException;
use Elastica\Query\AbstractQuery;
use Elastica\QueryBuilder\DSL;

/**
 * Elasticsearch aggregation DSL.
 *
 * @author Manuel Andreo Garcia <andreo.garcia@googlemail.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations.html
 */
class Aggregation implements DSL
{
    /**
     * must return type for QueryBuilder usage.
     */
    public function getType(): string
    {
        return DSL::TYPE_AGGREGATION;
    }

    /**
     * min aggregation.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-metrics-min-aggregation.html
     */
    public function min(string $name): Min
    {
        return new Min($name);
    }

    /**
     * max aggregation.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-metrics-max-aggregation.html
     */
    public function max(string $name): Max
    {
        return new Max($name);
    }

    /**
     * sum aggregation.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-metrics-sum-aggregation.html
     */
    public function sum(string $name): Sum
    {
        return new Sum($name);
    }

    /**
     * sum bucket aggregation.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-pipeline-sum-bucket-aggregation.html
     */
    public function sum_bucket(string $name, ?string $bucketsPath = null): SumBucket
    {
        return new SumBucket($name, $bucketsPath);
    }

    /**
     * avg aggregation.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-metrics-avg-aggregation.html
     */
    public function avg(string $name): Avg
    {
        return new Avg($name);
    }

    /**
     * avg bucket aggregation.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-pipeline-avg-bucket-aggregation.html
     */
    public function avg_bucket(string $name, ?string $bucketsPath = null): AvgBucket
    {
        return new AvgBucket($name, $bucketsPath);
    }

    /**
     * stats aggregation.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-metrics-stats-aggregation.html
     */
    public function stats(string $name): Stats
    {
        return new Stats($name);
    }

    /**
     * extended stats aggregation.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-metrics-extendedstats-aggregation.html
     */
    public function extended_stats(string $name): ExtendedStats
    {
        return new ExtendedStats($name);
    }

    /**
     * value count aggregation.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-metrics-valuecount-aggregation.html
     */
    public function value_count(string $name, string $field): ValueCount
    {
        return new ValueCount($name, $field);
    }

    /**
     * percentiles aggregation.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-metrics-percentile-aggregation.html
     *
     * @param string $name  the name of this aggregation
     * @param string $field the field on which to perform this aggregation
     */
    public function percentiles(string $name, ?string $field = null): Percentiles
    {
        return new Percentiles($name, $field);
    }

    /**
     * percentiles_bucket aggregation.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-pipeline-percentiles-bucket-aggregation.html
     *
     * @param string $name        the name of this aggregation
     * @param string $bucketsPath the field on which to perform this aggregation
     */
    public function percentiles_bucket(string $name, ?string $bucketsPath = null): PercentilesBucket
    {
        return new PercentilesBucket($name, $bucketsPath);
    }

    /**
     * cardinality aggregation.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-metrics-cardinality-aggregation.html
     */
    public function cardinality(string $name): Cardinality
    {
        return new Cardinality($name);
    }

    /**
     * geo bounds aggregation.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-metrics-geobounds-aggregation.html
     *
     * @param string $name
     */
    public function geo_bounds($name): void
    {
        throw new NotImplementedException();
    }

    /**
     * top hits aggregation.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-metrics-top-hits-aggregation.html
     */
    public function top_hits(string $name): TopHits
    {
        return new TopHits($name);
    }

    /**
     * scripted metric aggregation.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-metrics-scripted-metric-aggregation.html
     */
    public function scripted_metric(
        string $name,
        ?string $initScript = null,
        ?string $mapScript = null,
        ?string $combineScript = null,
        ?string $reduceScript = null
    ): ScriptedMetric {
        return new ScriptedMetric($name, $initScript, $mapScript, $combineScript, $reduceScript);
    }

    /**
     * global aggregation.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-global-aggregation.html
     */
    public function global_agg(string $name): GlobalAggregation
    {
        return new GlobalAggregation($name);
    }

    /**
     * filter aggregation.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-filter-aggregation.html
     *
     * @param AbstractQuery $filter
     */
    public function filter(string $name, ?AbstractQuery $filter = null): Filter
    {
        return new Filter($name, $filter);
    }

    /**
     * filters aggregation.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-filters-aggregation.html
     */
    public function filters(string $name): Filters
    {
        return new Filters($name);
    }

    /**
     * missing aggregation.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-missing-aggregation.html
     */
    public function missing(string $name, string $field): Missing
    {
        return new Missing($name, $field);
    }

    /**
     * nested aggregation.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-nested-aggregation.html
     *
     * @param string $path the nested path for this aggregation
     */
    public function nested(string $name, string $path): Nested
    {
        return new Nested($name, $path);
    }

    /**
     * reverse nested aggregation.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-reverse-nested-aggregation.html
     *
     * @param string $name The name of this aggregation
     * @param string $path Optional path to the nested object for this aggregation. Defaults to the root of the main document.
     */
    public function reverse_nested(string $name, ?string $path = null): ReverseNested
    {
        return new ReverseNested($name, $path);
    }

    /**
     * terms aggregation.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-terms-aggregation.html
     */
    public function terms(string $name): Terms
    {
        return new Terms($name);
    }

    /**
     * significant terms aggregation.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-significantterms-aggregation.html
     */
    public function significant_terms(string $name): SignificantTerms
    {
        return new SignificantTerms($name);
    }

    /**
     * range aggregation.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-range-aggregation.html
     */
    public function range(string $name): Range
    {
        return new Range($name);
    }

    /**
     * date range aggregation.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-daterange-aggregation.html
     */
    public function date_range(string $name): DateRange
    {
        return new DateRange($name);
    }

    /**
     * ipv4 range aggregation.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-iprange-aggregation.html
     */
    public function ipv4_range(string $name, string $field): IpRange
    {
        return new IpRange($name, $field);
    }

    /**
     * histogram aggregation.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-histogram-aggregation.html
     *
     * @param string $name     the name of this aggregation
     * @param string $field    the name of the field on which to perform the aggregation
     * @param int    $interval the interval by which documents will be bucketed
     */
    public function histogram(string $name, string $field, $interval): Histogram
    {
        return new Histogram($name, $field, $interval);
    }

    /**
     * date histogram aggregation.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-datehistogram-aggregation.html
     *
     * @param string     $name     the name of this aggregation
     * @param string     $field    the name of the field on which to perform the aggregation
     * @param int|string $interval the interval by which documents will be bucketed
     */
    public function date_histogram(string $name, string $field, $interval): DateHistogram
    {
        return new DateHistogram($name, $field, $interval);
    }

    /**
     * geo distance aggregation.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-geodistance-aggregation.html
     *
     * @param string       $name   the name if this aggregation
     * @param string       $field  the field on which to perform this aggregation
     * @param array|string $origin the point from which distances will be calculated
     */
    public function geo_distance(string $name, string $field, $origin): GeoDistance
    {
        return new GeoDistance($name, $field, $origin);
    }

    /**
     * geohash grid aggregation.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-geohashgrid-aggregation.html
     *
     * @param string $name  the name of this aggregation
     * @param string $field the field on which to perform this aggregation
     */
    public function geohash_grid(string $name, string $field): GeohashGrid
    {
        return new GeohashGrid($name, $field);
    }

    /**
     * bucket script aggregation.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-pipeline-bucket-script-aggregation.html
     */
    public function bucket_script(string $name, ?array $bucketsPath = null, ?string $script = null): BucketScript
    {
        return new BucketScript($name, $bucketsPath, $script);
    }

    /**
     * serial diff aggregation.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-pipeline-serialdiff-aggregation.html
     */
    public function serial_diff(string $name, ?string $bucketsPath = null): SerialDiff
    {
        return new SerialDiff($name, $bucketsPath);
    }

    /**
     * adjacency matrix aggregation.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-adjacency-matrix-aggregation.html
     */
    public function adjacency_matrix(string $name): AdjacencyMatrix
    {
        return new AdjacencyMatrix($name);
    }

    /** sampler aggregation.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-sampler-aggregation.html
     *
     * @param string $name
     */
    public function sampler($name): Sampler
    {
        return new Sampler($name);
    }

    /**
     * diversified sampler aggregation.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-diversified-sampler-aggregation.html
     */
    public function diversified_sampler(string $name): DiversifiedSampler
    {
        return new DiversifiedSampler($name);
    }
}
