<?php
namespace Elastica\QueryBuilder\DSL;

use Elastica\Aggregation\Avg;
use Elastica\Aggregation\Cardinality;
use Elastica\Aggregation\DateHistogram;
use Elastica\Aggregation\DateRange;
use Elastica\Aggregation\ExtendedStats;
use Elastica\Aggregation\Filter as FilterAggregation;
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
use Elastica\Aggregation\Range;
use Elastica\Aggregation\ReverseNested;
use Elastica\Aggregation\ScriptedMetric;
use Elastica\Aggregation\SignificantTerms;
use Elastica\Aggregation\Stats;
use Elastica\Aggregation\Sum;
use Elastica\Aggregation\Terms;
use Elastica\Aggregation\TopHits;
use Elastica\Aggregation\ValueCount;
use Elastica\Exception\NotImplementedException;
use Elastica\Filter\AbstractFilter;
use Elastica\QueryBuilder\DSL;

/**
 * elasticsearch aggregation DSL.
 *
 * @author Manuel Andreo Garcia <andreo.garcia@googlemail.com>
 *
 * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations.html
 */
class Aggregation implements DSL
{
    /**
     * must return type for QueryBuilder usage.
     *
     * @return string
     */
    public function getType()
    {
        return DSL::TYPE_AGGREGATION;
    }

    /**
     * min aggregation.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-metrics-min-aggregation.html
     *
     * @param string $name
     *
     * @return Min
     */
    public function min($name)
    {
        return new Min($name);
    }

    /**
     * max aggregation.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-metrics-max-aggregation.html
     *
     * @param string $name
     *
     * @return Max
     */
    public function max($name)
    {
        return new Max($name);
    }

    /**
     * sum aggregation.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-metrics-sum-aggregation.html
     *
     * @param string $name
     *
     * @return Sum
     */
    public function sum($name)
    {
        return new Sum($name);
    }

    /**
     * avg aggregation.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-metrics-avg-aggregation.html
     *
     * @param string $name
     *
     * @return Avg
     */
    public function avg($name)
    {
        return new Avg($name);
    }

    /**
     * stats aggregation.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-metrics-stats-aggregation.html
     *
     * @param string $name
     *
     * @return Stats
     */
    public function stats($name)
    {
        return new Stats($name);
    }

    /**
     * extended stats aggregation.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-metrics-extendedstats-aggregation.html
     *
     * @param string $name
     *
     * @return ExtendedStats
     */
    public function extended_stats($name)
    {
        return new ExtendedStats($name);
    }

    /**
     * value count aggregation.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-metrics-valuecount-aggregation.html
     *
     * @param string $name
     * @param string $field
     *
     * @return ValueCount
     */
    public function value_count($name, $field)
    {
        return new ValueCount($name, $field);
    }

    /**
     * percentiles aggregation.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-metrics-percentile-aggregation.html
     *
     * @param string $name  the name of this aggregation
     * @param string $field the field on which to perform this aggregation
     *
     * @return Percentiles
     */
    public function percentiles($name, $field = null)
    {
        return new Percentiles($name, $field);
    }

    /**
     * percentile ranks aggregation.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-metrics-percentile-rank-aggregation.html
     *
     * @param string $name
     */
    public function percentile_ranks($name)
    {
        throw new NotImplementedException();
    }

    /**
     * cardinality aggregation.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-metrics-cardinality-aggregation.html
     *
     * @param string $name
     *
     * @return Cardinality
     */
    public function cardinality($name)
    {
        return new Cardinality($name);
    }

    /**
     * geo bounds aggregation.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-metrics-geobounds-aggregation.html
     *
     * @param string $name
     */
    public function geo_bounds($name)
    {
        throw new NotImplementedException();
    }

    /**
     * top hits aggregation.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-metrics-top-hits-aggregation.html
     *
     * @param string $name
     *
     * @return TopHits
     */
    public function top_hits($name)
    {
        return new TopHits($name);
    }

    /**
     * scripted metric aggregation.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-metrics-scripted-metric-aggregation.html
     *
     * @param string      $name
     * @param string|null $initScript
     * @param string|null $mapScript
     * @param string|null $combineScript
     * @param string|null $reduceScript
     *
     * @return ScriptedMetric
     */
    public function scripted_metric($name, $initScript = null, $mapScript = null, $combineScript = null, $reduceScript = null)
    {
        return new ScriptedMetric($name, $initScript, $mapScript, $combineScript, $reduceScript);
    }

    /**
     * global aggregation.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-global-aggregation.html
     *
     * @param string $name
     *
     * @return GlobalAggregation
     */
    public function global_agg($name)
    {
        return new GlobalAggregation($name);
    }

    /**
     * filter aggregation.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-filter-aggregation.html
     *
     * @param string         $name
     * @param AbstractFilter $filter
     *
     * @return FilterAggregation
     */
    public function filter($name, AbstractFilter $filter = null)
    {
        return new FilterAggregation($name, $filter);
    }

    /**
     * filters aggregation.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-filters-aggregation.html
     *
     * @param string $name
     *
     * @return Filters
     */
    public function filters($name)
    {
        return new Filters($name);
    }

    /**
     * missing aggregation.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-missing-aggregation.html
     *
     * @param string $name
     * @param string $field
     *
     * @return Missing
     */
    public function missing($name, $field)
    {
        return new Missing($name, $field);
    }

    /**
     * nested aggregation.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-nested-aggregation.html
     *
     * @param string $name
     * @param string $path the nested path for this aggregation
     *
     * @return Nested
     */
    public function nested($name, $path)
    {
        return new Nested($name, $path);
    }

    /**
     * reverse nested aggregation.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-reverse-nested-aggregation.html
     *
     * @param string $name The name of this aggregation
     * @param string $path Optional path to the nested object for this aggregation. Defaults to the root of the main document.
     *
     * @return ReverseNested
     */
    public function reverse_nested($name, $path = null)
    {
        return new ReverseNested($name);
    }

    /**
     * children aggregation.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-children-aggregation.html
     *
     * @param string $name
     */
    public function children($name)
    {
        throw new NotImplementedException();
    }

    /**
     * terms aggregation.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-terms-aggregation.html
     *
     * @param string $name
     *
     * @return Terms
     */
    public function terms($name)
    {
        return new Terms($name);
    }

    /**
     * significant terms aggregation.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-significantterms-aggregation.html
     *
     * @param string $name
     *
     * @return SignificantTerms
     */
    public function significant_terms($name)
    {
        return new SignificantTerms($name);
    }

    /**
     * range aggregation.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-range-aggregation.html
     *
     * @param string $name
     *
     * @return Range
     */
    public function range($name)
    {
        return new Range($name);
    }

    /**
     * date range aggregation.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-daterange-aggregation.html
     *
     * @param string $name
     *
     * @return DateRange
     */
    public function date_range($name)
    {
        return new DateRange($name);
    }

    /**
     * ipv4 range aggregation.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-iprange-aggregation.html
     *
     * @param string $name
     * @param string $field
     *
     * @return IpRange
     */
    public function ipv4_range($name, $field)
    {
        return new IpRange($name, $field);
    }

    /**
     * histogram aggregation.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-histogram-aggregation.html
     *
     * @param string $name     the name of this aggregation
     * @param string $field    the name of the field on which to perform the aggregation
     * @param int    $interval the interval by which documents will be bucketed
     *
     * @return Histogram
     */
    public function histogram($name, $field, $interval)
    {
        return new Histogram($name, $field, $interval);
    }

    /**
     * date histogram aggregation.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-datehistogram-aggregation.html
     *
     * @param string $name     the name of this aggregation
     * @param string $field    the name of the field on which to perform the aggregation
     * @param int    $interval the interval by which documents will be bucketed
     *
     * @return DateHistogram
     */
    public function date_histogram($name, $field, $interval)
    {
        return new DateHistogram($name, $field, $interval);
    }

    /**
     * geo distance aggregation.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-geodistance-aggregation.html
     *
     * @param string       $name   the name if this aggregation
     * @param string       $field  the field on which to perform this aggregation
     * @param string|array $origin the point from which distances will be calculated
     *
     * @return GeoDistance
     */
    public function geo_distance($name, $field, $origin)
    {
        return new GeoDistance($name, $field, $origin);
    }

    /**
     * geohash grid aggregation.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-geohashgrid-aggregation.html
     *
     * @param string $name  the name of this aggregation
     * @param string $field the field on which to perform this aggregation
     *
     * @return GeohashGrid
     */
    public function geohash_grid($name, $field)
    {
        return new GeohashGrid($name, $field);
    }
}
