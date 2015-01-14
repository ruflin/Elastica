<?php

namespace Elastica\QueryBuilder\DSL;

use Elastica\Filter\AbstractFilter;
use Elastica\Filter\Bool;
use Elastica\Filter\BoolAnd;
use Elastica\Filter\BoolNot;
use Elastica\Filter\BoolOr;
use Elastica\Filter\Exists;
use Elastica\Filter\GeoBoundingBox;
use Elastica\Filter\GeoDistance;
use Elastica\Filter\GeoDistanceRange;
use Elastica\Filter\GeohashCell;
use Elastica\Filter\GeoPolygon;
use Elastica\Filter\GeoShapePreIndexed;
use Elastica\Filter\GeoShapeProvided;
use Elastica\Filter\HasChild;
use Elastica\Filter\HasParent;
use Elastica\Filter\Ids;
use Elastica\Filter\Indices;
use Elastica\Filter\Limit;
use Elastica\Filter\MatchAll;
use Elastica\Filter\Missing;
use Elastica\Filter\Nested;
use Elastica\Filter\NumericRange;
use Elastica\Filter\Prefix;
use Elastica\Filter\Query as QueryFilter;
use Elastica\Filter\Range;
use Elastica\Filter\Regexp;
use Elastica\Filter\Script;
use Elastica\Filter\Term;
use Elastica\Filter\Terms;
use Elastica\Filter\Type;
use Elastica\Query\AbstractQuery;
use Elastica\QueryBuilder\DSL;

/**
 * elasticsearch filter DSL
 *
 * @package Elastica
 * @author Manuel Andreo Garcia <andreo.garcia@googlemail.com>
 * @link http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-filters.html
 */
class Filter implements DSL
{
    /**
     * must return type for QueryBuilder usage
     *
     * @return string
     */
    public function getType()
    {
        return self::TYPE_FILTER;
    }

    /**
     * and filter
     *
     * @link http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-and-filter.html
     * @param  AbstractFilter[] $filters
     * @return BoolAnd
     */
    public function bool_and(array $filters)
    {
        $and = new BoolAnd();
        $and->setFilters($filters);

        return $and;
    }

    /**
     * bool filter
     *
     * @link http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-bool-filter.html
     * @return \Elastica\Filter\Bool
     */
    public function bool()
    {
        return new Bool();
    }

    /**
     * exists filter
     *
     * @link http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-exists-filter.html
     * @param  string $field
     * @return Exists
     */
    public function exists($field)
    {
        return new Exists($field);
    }

    /**
     * geo bounding box filter
     *
     * @link http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-geo-bounding-box-filter.html
     * @param  string         $field
     * @param  array          $coordinates
     * @return GeoBoundingBox
     */
    public function geo_bounding_box($field, array $coordinates)
    {
        return new GeoBoundingBox($field, $coordinates);
    }

    /**
     * geo distance filter
     *
     * @link http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-geo-distance-filter.htm
     * @param  string       $key      Key
     * @param  array|string $location Location as array or geohash: array('lat' => 48.86, 'lon' => 2.35) OR 'drm3btev3e86'
     * @param  string       $distance Distance
     * @return GeoDistance
     */
    public function geo_distance($key, $location, $distance)
    {
        return new GeoDistance($key, $location, $distance);
    }

    /**
     * geo distance rangefilter
     *
     * @link http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-geo-distance-range-filter.html
     * @param  string           $key
     * @param  array|string     $location
     * @param  array            $ranges
     * @return GeoDistanceRange
     */
    public function geo_distance_range($key, $location, array $ranges = array())
    {
        return new GeoDistanceRange($key, $location, $ranges);
    }

    /**
     * geo polygon filter
     *
     * @link http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-geo-polygon-filter.html
     * @param  string     $key    Key
     * @param  array      $points Points making up polygon
     * @return GeoPolygon
     */
    public function geo_polygon($key, array $points)
    {
        return new GeoPolygon($key, $points);
    }

    /**
     * provided geo shape filter
     *
     * @link http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-geo-shape-filter.html
     * @param  string           $path
     * @param  array            $coordinates
     * @param  string           $shapeType
     * @return GeoShapeProvided
     */
    public function geo_shape_provided($path, array $coordinates, $shapeType = GeoShapeProvided::TYPE_ENVELOPE)
    {
        return new GeoShapeProvided($path, $coordinates, $shapeType);
    }

    /**
     * pre indexed geo shape filter
     *
     * @link http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-geo-shape-filter.html
     * @param  string             $path         The path/field of the shape searched
     * @param  string             $indexedId    Id of the pre-indexed shape
     * @param  string             $indexedType  Type of the pre-indexed shape
     * @param  string             $indexedIndex Index of the pre-indexed shape
     * @param  string             $indexedPath  Path of the pre-indexed shape
     * @return GeoShapePreIndexed
     */
    public function geo_shape_pre_indexed($path, $indexedId, $indexedType, $indexedIndex, $indexedPath)
    {
        return new GeoShapePreIndexed($path, $indexedId, $indexedType, $indexedIndex, $indexedPath);
    }

    /**
     * geohash cell filter
     *
     * @link http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-geohash-cell-filter.html
     * @param  string       $field     The field on which to filter
     * @param  array|string $location  Location as coordinates array or geohash string ['lat' => 40.3, 'lon' => 45.2]
     * @param  int|string   $precision length of geohash prefix or distance (3, or "50m")
     * @param  bool         $neighbors If true, filters cells next to the given cell.
     * @return GeohashCell
     */
    public function geohash_cell($field, $location, $precision = -1, $neighbors = false)
    {
        return new GeohashCell($field, $location, $precision, $neighbors);
    }

    /**
     * has child filter
     *
     * @link http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-has-child-filter.html
     * @param  AbstractQuery|AbstractFilter $query
     * @param  string                       $type
     * @return HasChild
     */
    public function has_child($query, $type)
    {
        return new HasChild($query, $type);
    }

    /**
     * has parent filter
     *
     * @link http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-has-parent-filter.html
     * @param  AbstractQuery|AbstractFilter $query
     * @param  string                       $type
     * @return HasParent
     */
    public function has_parent($query, $type)
    {
        return new HasParent($query, $type);
    }

    /**
     * ids filter
     *
     * @link http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-ids-filter.html
     * @param  string|\Elastica\Type $type
     * @param  array                 $ids
     * @return Ids
     */
    public function ids($type, array $ids)
    {
        return new Ids($type, $ids);
    }

    /**
     * indices filter
     *
     * @link http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-indices-filter.html
     * @param  AbstractFilter $filter  filter which will be applied to docs in the specified indices
     * @param  string[]       $indices
     * @return Indices
     */
    public function indices(AbstractFilter $filter, array $indices)
    {
        return new Indices($filter, $indices);
    }

    /**
     * limit filter
     *
     * @link http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-limit-filter.html
     * @param  int   $limit Limit
     * @return Limit
     */
    public function limit($limit)
    {
        return new Limit($limit);
    }

    /**
     * match all filter
     *
     * @link http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-match-all-filter.html
     * @return MatchAll
     */
    public function match_all()
    {
        return new MatchAll();
    }

    /**
     * missing filter
     *
     * @link http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-missing-filter.html
     * @param  string  $field
     * @return Missing
     */
    public function missing($field)
    {
        return new Missing($field);
    }

    /**
     * nested filter
     *
     * @link http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-nested-filter.html
     * @return Nested
     */
    public function nested()
    {
        return new Nested();
    }

    /**
     * not filter
     *
     * @link http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-not-filter.html
     * @param  AbstractFilter $filter
     * @return BoolNot
     */
    public function bool_not(AbstractFilter $filter)
    {
        return new BoolNot($filter);
    }

    /**
     * numeric range filter
     *
     * @link http://www.elasticsearch.org/guide/en/elasticsearch/reference/0.90/query-dsl-numeric-range-filter.html
     * @return NumericRange
     */
    public function numeric_range()
    {
        return new NumericRange();
    }

    /**
     * or filter
     *
     * @link http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-or-filter.html
     * @param  AbstractFilter[] $filters
     * @return BoolOr
     */
    public function bool_or($filters)
    {
        $or = new BoolOr();
        $or->setFilters($filters);

        return $or;
    }

    /**
     * prefix filter
     *
     * @link http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-prefix-filter.html
     * @param  string $field
     * @param  string $prefix
     * @return Prefix
     */
    public function prefix($field, $prefix)
    {
        return new Prefix($field, $prefix);
    }

    /**
     * query filter
     *
     * @link http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-query-filter.html
     * @param  AbstractQuery $query
     * @return QueryFilter
     */
    public function query(AbstractQuery $query)
    {
        return new QueryFilter($query);
    }

    /**
     * range filter
     *
     * @link http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-range-filter.html
     * @param  string $fieldName
     * @param  array  $args
     * @return Range
     */
    public function range($fieldName, array $args)
    {
        return new Range($fieldName, $args);
    }

    /**
     * regexp filter
     *
     * @link http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-regexp-filter.html
     * @param  string $field
     * @param  string $regexp
     * @return Regexp
     */
    public function regexp($field, $regexp)
    {
        return new Regexp($field, $regexp);
    }

    /**
     * script filter
     *
     * @link http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-script-filter.html
     * @param  array|string|\Elastica\Script $script
     * @return Script
     */
    public function script($script)
    {
        return new Script($script);
    }

    /**
     * term filter
     *
     * @link http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-term-filter.html
     * @param  array $term
     * @return Term
     */
    public function term(array $term = array())
    {
        return new Term($term);
    }

    /**
     * terms filter
     *
     * @link http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-terms-filter.html
     * @param  string $field
     * @param  array  $terms
     * @return Terms
     */
    public function terms($field, array $terms)
    {
        return new Terms($field, $terms);
    }

    /**
     * type filter
     *
     * @link http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-type-filter.html
     * @param  string $type
     * @return Type
     */
    public function type($type)
    {
        return new Type($type);
    }
}
