<?php

namespace Elastica\QueryBuilder\Version;

use Elastica\QueryBuilder\Version;

/**
 * elasticsearch 0.9 DSL
 *
 * @link http://www.elasticsearch.org/guide/en/elasticsearch/reference/0.90/index.html
 * @package Elastica
 * @author Manuel Andreo Garcia <andreo.garcia@googlemail.com>
 */
class Version090 extends Version
{
    protected $queries = array(
        'match',
        'multi_match',
        'bool',
        'boosting',
        'common_terms',
        'custom_filters_score',
        'custom_score',
        'custom_boost_factor',
        'constant_score',
        'dis_max',
        'field',
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
        'text',
        'minimum_should_match',
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
        'numeric_range',
        'bool_or', // original: or
        'prefix',
        'query',
        'range',
        'regexp',
        'script',
        'term',
        'terms',
        'type',
    );

    protected $suggesters = array(
        'term',
        'phrase',
        'completion',
    );
}
