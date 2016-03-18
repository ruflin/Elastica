<?php

namespace Elastica\QueryBuilder\Version;

use Elastica\QueryBuilder\Version;

/**
 * elasticsearch 1.0 DSL.
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/1.x/index.html
 *
 * @author Manuel Andreo Garcia <andreo.garcia@googlemail.com>
 */
class Version100 extends Version
{
    protected $queries = array(
        'match',
        'multi_match',
        'bool',
        'boosting',
        'common_terms',
        'constant_score',
        'dis_max',
        'filtered',
        'fuzzy_like_this',
        'fuzzy_like_this_field',
        'function_score',
        'fuzzy',
        'geo_shape',
        'has_child',
        'has_parent',
        'ids',
        'indices',
        'match_all',
        'more_like_this',
        'more_like_this_field',
        'nested',
        'prefix',
        'query_string',
        'simple_query_string',
        'range',
        'regexp',
        'span_first',
        'span_multi_term',
        'span_near',
        'span_not',
        'span_or',
        'span_term',
        'term',
        'terms',
        'top_children',
        'wildcard',
        'minimum_should_match',

        // removed in 1.0.0
        // 'text'
        // 'field'
        // 'custom_filters_score'
        // 'custom_score'
        // 'custom_boost_factor'
    );

    protected $filters = array(
        'bool_and', // original: bool
        'bool',
        'exists',
        'geo_bounding_box',
        'geo_distance',
        'geo_distance_range',
        'geo_polygon',
        'geo_shape_provided', // original: geo_shape
        'geo_shape_pre_indexed', // original: geo_shape
        'geohash_cell',
        'has_child',
        'has_parent',
        'ids',
        'indices',
        'limit',
        'match_all',
        'missing',
        'nested',
        'bool_not', // original: not
        'bool_or', // original: or
        'prefix',
        'query',
        'range',
        'regexp',
        'script',
        'term',
        'terms',
        'type',

        // removed in 1.0.0
        // 'numeric_range'
    );

    protected $aggregations = array(
        'min',
        'max',
        'sum',
        'avg',
        'stats',
        'extended_stats',
        'value_count',
        'global_agg', // original: global
        'filter',
        'missing',
        'nested',
        'terms',
        'range',
        'date_range',
        'ipv4_range',
        'histogram',
        'date_histogram',
        'geo_distance',
        'geohash_grid',
    );

    protected $suggesters = array(
        'term',
        'phrase',
        'completion',
    );
}
