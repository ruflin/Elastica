<?php

namespace Elastica\QueryBuilder\DSL;

use Elastica\Query\AbstractQuery;
use Elastica\Query\AbstractSpanQuery;
use Elastica\Query as BaseQuery;
use Elastica\Query\BoolQuery;
use Elastica\Query\Boosting;
use Elastica\Query\Common;
use Elastica\Query\ConstantScore;
use Elastica\Query\DisMax;
use Elastica\Query\DistanceFeature;
use Elastica\Query\Exists;
use Elastica\Query\FunctionScore;
use Elastica\Query\Fuzzy;
use Elastica\Query\GeoBoundingBox;
use Elastica\Query\GeoDistance;
use Elastica\Query\GeoPolygon;
use Elastica\Query\HasChild;
use Elastica\Query\HasParent;
use Elastica\Query\Ids;
use Elastica\Query\Match;
use Elastica\Query\MatchAll;
use Elastica\Query\MatchNone;
use Elastica\Query\MatchPhrase;
use Elastica\Query\MatchPhrasePrefix;
use Elastica\Query\MoreLikeThis;
use Elastica\Query\MultiMatch;
use Elastica\Query\Nested;
use Elastica\Query\ParentId;
use Elastica\Query\Percolate;
use Elastica\Query\Prefix;
use Elastica\Query\QueryString;
use Elastica\Query\Range;
use Elastica\Query\Regexp;
use Elastica\Query\SimpleQueryString;
use Elastica\Query\SpanContaining;
use Elastica\Query\SpanFirst;
use Elastica\Query\SpanMulti;
use Elastica\Query\SpanNear;
use Elastica\Query\SpanNot;
use Elastica\Query\SpanOr;
use Elastica\Query\SpanTerm;
use Elastica\Query\SpanWithin;
use Elastica\Query\Term;
use Elastica\Query\Terms;
use Elastica\Query\Wildcard;
use Elastica\QueryBuilder\DSL;

/**
 * elasticsearch query DSL.
 *
 * @author Manuel Andreo Garcia <andreo.garcia@googlemail.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-queries.html
 */
class Query implements DSL
{
    /**
     * must return type for QueryBuilder usage.
     */
    public function getType(): string
    {
        return self::TYPE_QUERY;
    }

    /**
     * match query.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-match-query.html
     *
     * @param mixed $values
     */
    public function match(?string $field = null, $values = null): Match
    {
        return new Match($field, $values);
    }

    /**
     * multi match query.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-multi-match-query.html
     */
    public function multi_match(): MultiMatch
    {
        return new MultiMatch();
    }

    /**
     * bool query.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-bool-query.html
     */
    public function bool(): BoolQuery
    {
        return new BoolQuery();
    }

    /**
     * boosting query.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-boosting-query.html
     */
    public function boosting(): Boosting
    {
        return new Boosting();
    }

    /**
     * common terms query.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-common-terms-query.html
     *
     * @param float $cutoffFrequency percentage in decimal form (.001 == 0.1%)
     */
    public function common_terms(string $field, string $query, float $cutoffFrequency): Common
    {
        return new Common($field, $query, $cutoffFrequency);
    }

    /**
     * constant score query.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-constant-score-query.html
     */
    public function constant_score(?AbstractQuery $filter = null): ConstantScore
    {
        return new ConstantScore($filter);
    }

    /**
     * dis max query.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-dis-max-query.html
     */
    public function dis_max(): DisMax
    {
        return new DisMax();
    }

    /**
     * distance feature query.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-distance-feature-query.html
     *
     * @param array|string $origin
     */
    public function distance_feature(string $field, $origin, string $pivot): DistanceFeature
    {
        return new DistanceFeature($field, $origin, $pivot);
    }

    /**
     * function score query.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-function-score-query.html
     */
    public function function_score(): FunctionScore
    {
        return new FunctionScore();
    }

    /**
     * fuzzy query.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-fuzzy-query.html
     *
     * @param string $value String to search for
     */
    public function fuzzy(?string $fieldName = null, ?string $value = null): Fuzzy
    {
        return new Fuzzy($fieldName, $value);
    }

    /**
     * geo bounding box query.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-geo-bounding-box-query.html
     */
    public function geo_bounding_box(string $key, array $coordinates): GeoBoundingBox
    {
        return new GeoBoundingBox($key, $coordinates);
    }

    /**
     * geo distance query.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-geo-distance-query.html
     *
     * @param array|string $location
     */
    public function geo_distance(string $key, $location, string $distance): GeoDistance
    {
        return new GeoDistance($key, $location, $distance);
    }

    /**
     * geo polygon query.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-geo-polygon-query.html
     */
    public function geo_polygon(string $key, array $points): GeoPolygon
    {
        return new GeoPolygon($key, $points);
    }

    /**
     * has child query.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-has-child-query.html
     *
     * @param AbstractQuery|BaseQuery|string $query
     * @param string                         $type  Parent document type
     */
    public function has_child($query, ?string $type = null): HasChild
    {
        return new HasChild($query, $type);
    }

    /**
     * has parent query.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-has-parent-query.html
     *
     * @param AbstractQuery|BaseQuery|string $query
     * @param string                         $type  Parent document type
     */
    public function has_parent($query, string $type): HasParent
    {
        return new HasParent($query, $type);
    }

    /**
     * ids query.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-ids-query.html
     */
    public function ids(array $ids = []): Ids
    {
        return new Ids($ids);
    }

    /**
     * match all query.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-match-all-query.html
     */
    public function match_all(): MatchAll
    {
        return new MatchAll();
    }

    /**
     * match none query.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-match-all-query.html#query-dsl-match-none-query
     */
    public function match_none(): MatchNone
    {
        return new MatchNone();
    }

    /**
     * match phrase query.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-match-query-phrase.html
     *
     * @param mixed|null $values
     */
    public function match_phrase(?string $field = null, $values = null): MatchPhrase
    {
        return new MatchPhrase($field, $values);
    }

    /**
     * match phrase prefix query.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-match-query-phrase-prefix.html
     *
     * @param mixed|null $values
     */
    public function match_phrase_prefix(?string $field = null, $values = null): MatchPhrasePrefix
    {
        return new MatchPhrasePrefix($field, $values);
    }

    /**
     * more like this query.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-mlt-query.html
     */
    public function more_like_this(): MoreLikeThis
    {
        return new MoreLikeThis();
    }

    /**
     * nested query.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-nested-query.html
     */
    public function nested(): Nested
    {
        return new Nested();
    }

    /**
     * @param int|string $id
     */
    public function parent_id(string $type, $id, bool $ignoreUnmapped = false): ParentId
    {
        return new ParentId($type, $id, $ignoreUnmapped);
    }

    /**
     * prefix query.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-prefix-query.html
     *
     * @param array $prefix Prefix array
     */
    public function prefix(array $prefix = []): Prefix
    {
        return new Prefix($prefix);
    }

    /**
     * query string query.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-query-string-query.html
     *
     * @param string $queryString OPTIONAL Query string for object
     */
    public function query_string(string $queryString = ''): QueryString
    {
        return new QueryString($queryString);
    }

    /**
     * simple_query_string query.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-simple-query-string-query.html
     */
    public function simple_query_string(string $query, array $fields = []): SimpleQueryString
    {
        return new SimpleQueryString($query, $fields);
    }

    /**
     * range query.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-range-query.html
     */
    public function range(?string $fieldName = null, array $args = []): Range
    {
        return new Range($fieldName, $args);
    }

    /**
     * regexp query.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-regexp-query.html
     */
    public function regexp(string $key = '', ?string $value = null, float $boost = 1.0): Regexp
    {
        return new Regexp($key, $value, $boost);
    }

    /**
     * span first query.
     *
     * @param AbstractQuery|array $match
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-span-first-query.html
     */
    public function span_first($match = null, ?int $end = null): SpanFirst
    {
        return new SpanFirst($match, $end);
    }

    /**
     * span multi term query.
     *
     * @param AbstractQuery|array $match
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-span-multi-term-query.html
     */
    public function span_multi_term($match = null): SpanMulti
    {
        return new SpanMulti($match);
    }

    /**
     * span near query.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-span-near-query.html
     */
    public function span_near(array $clauses = [], int $slop = 1, bool $inOrder = false): SpanNear
    {
        return new SpanNear($clauses, $slop, $inOrder);
    }

    /**
     * span not query.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-span-not-query.html
     */
    public function span_not(?AbstractSpanQuery $include = null, ?AbstractSpanQuery $exclude = null): SpanNot
    {
        return new SpanNot($include, $exclude);
    }

    /**
     * span_or query.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-span-or-query.html
     */
    public function span_or(array $clauses = []): SpanOr
    {
        return new SpanOr($clauses);
    }

    /**
     * span_term query.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-span-term-query.html
     */
    public function span_term(array $term = []): SpanTerm
    {
        return new SpanTerm($term);
    }

    /**
     * span_containing query.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-span-containing-query.html
     */
    public function span_containing(?AbstractSpanQuery $little = null, ?AbstractSpanQuery $big = null): SpanContaining
    {
        return new SpanContaining($little, $big);
    }

    /**
     * span_within query.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-span-within-query.html
     */
    public function span_within(?AbstractSpanQuery $little = null, ?AbstractSpanQuery $big = null): SpanWithin
    {
        return new SpanWithin($little, $big);
    }

    /**
     * term query.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-term-query.html
     */
    public function term(array $term = []): Term
    {
        return new Term($term);
    }

    /**
     * terms query.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-terms-query.html
     */
    public function terms(string $field, array $terms = []): Terms
    {
        return new Terms($field, $terms);
    }

    /**
     * wildcard query.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-wildcard-query.html
     */
    public function wildcard(string $field, string $value, float $boost = 1.0): Wildcard
    {
        return new Wildcard($field, $value, $boost);
    }

    /**
     * exists query.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-exists-query.html
     */
    public function exists(string $field): Exists
    {
        return new Exists($field);
    }

    /**
     * type query.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-percolate-query.html
     */
    public function percolate(): Percolate
    {
        return new Percolate();
    }
}
