<?php
namespace Elastica\QueryBuilder\Version;

use Elastica\QueryBuilder\Version;

/**
 * elasticsearch 2.4 DSL.
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/2.4/index.html
 *
 * @author Cariou Pierre-Yves <cariou.p@gmail.com>
 */
class Version240 extends Version
{
    protected $queries = [
        'match',
        'multi_match',
        'bool',
        'boosting',
        'common_terms',
        'constant_score',
        'dis_max',
        'function_score',
        'fuzzy',
        'geo_shape',
        'has_child',
        'has_parent',
        'ids',
        'match_all',
        'more_like_this',
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
        'wildcard',
        'geo_distance',
        'exists',
        'type',
        'percolate',
    ];

    protected $aggregations = [
        'min',
        'max',
        'sum',
        'avg',
        'stats',
        'extended_stats',
        'value_count',
        'percentiles',
        'percentile_ranks',
        'cardinality',
        'geo_bounds',
        'top_hits',
        'scripted_metric',
        'global_agg', // original: global
        'filter',
        'filters',
        'missing',
        'nested',
        'reverse_nested',
        'children',
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
    ];

    protected $suggesters = [
        'term',
        'phrase',
        'completion',
        'context',
    ];
}
