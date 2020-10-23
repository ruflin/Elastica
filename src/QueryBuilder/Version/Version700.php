<?php

namespace Elastica\QueryBuilder\Version;

use Elastica\QueryBuilder\Version;

/**
 * elasticsearch 7.0 DSL.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/7.0/index.html
 *
 * @author Cariou Pierre-Yves <cariou.p@gmail.com>
 */
class Version700 extends Version
{
    protected $queries = [
        'bool',
        'boosting',
        'common_terms',
        'constant_score',
        'dis_max',
        'distance_feature',
        'function_score',
        'fuzzy',
        'geo_bounding_box',
        'geo_distance',
        'geo_polygon',
        // 'geo_shape', // Not Implemented
        'has_child',
        'has_parent',
        'ids',
        'match',
        'match_all',
        'match_none',
        'match_phrase',
        'match_phrase_prefix',
        'more_like_this',
        'multi_match',
        'nested',
        'parent_id',
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
        'wildcard',
        'exists',
        'percolate',
    ];

    protected $aggregations = [
        'min',
        'max',
        'sum',
        'sum_bucket',
        'avg',
        'avg_bucket',
        'stats',
        'extended_stats',
        'value_count',
        'percentiles',
        'percentiles_bucket',
        // 'percentile_ranks', // Not implemented
        'cardinality',
        'geo_bounds',
        'top_hits',
        'scripted_metric',
        'global',
        'global_agg', // Deprecated
        'filter',
        'filters',
        'missing',
        'nested',
        'reverse_nested',
        // 'children', //Not implemented
        'terms',
        'significant_terms',
        'range',
        'date_range',
        'ipv4_range',
        'histogram',
        'date_histogram',
        'geo_distance',
        'geohash_grid',
        'bucket_script',
        'serial_diff',
        'weighted_avg',
        'composite',
    ];

    protected $suggesters = [
        'term',
        'phrase',
        'completion',
        // 'context', // Not implemented
    ];

    protected $collapsers = [
        'inner_hits',
    ];
}
