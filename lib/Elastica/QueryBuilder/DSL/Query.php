<?php

namespace Elastica\QueryBuilder\DSL;

use Elastica\Exception\NotImplementedException;
use Elastica\Query\AbstractQuery;
use Elastica\Query\AbstractSpanQuery;
use Elastica\Query as BaseQuery;
use Elastica\Query\BoolQuery;
use Elastica\Query\Boosting;
use Elastica\Query\Common;
use Elastica\Query\ConstantScore;
use Elastica\Query\DisMax;
use Elastica\Query\Exists;
use Elastica\Query\FunctionScore;
use Elastica\Query\Fuzzy;
use Elastica\Query\GeoDistance;
use Elastica\Query\HasChild;
use Elastica\Query\HasParent;
use Elastica\Query\Ids;
use Elastica\Query\Match;
use Elastica\Query\MatchAll;
use Elastica\Query\MatchNone;
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
     *
     * @return string
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
     * @param string $field
     * @param mixed  $values
     *
     * @return Match
     */
    public function match(string $field = null, $values = null): Match
    {
        return new Match($field, $values);
    }

    /**
     * multi match query.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-multi-match-query.html
     *
     * @return MultiMatch
     */
    public function multi_match(): MultiMatch
    {
        return new MultiMatch();
    }

    /**
     * bool query.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-bool-query.html
     *
     * @return BoolQuery
     */
    public function bool(): BoolQuery
    {
        return new BoolQuery();
    }

    /**
     * boosting query.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-boosting-query.html
     *
     * @return Boosting
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
     * @param string $field
     * @param string $query
     * @param float  $cutoffFrequency percentage in decimal form (.001 == 0.1%)
     *
     * @return Common
     */
    public function common_terms(string $field, string $query, float $cutoffFrequency): Common
    {
        return new Common($field, $query, $cutoffFrequency);
    }

    /**
     * constant score query.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-constant-score-query.html
     *
     * @param AbstractQuery|null $filter
     *
     * @return ConstantScore
     */
    public function constant_score(AbstractQuery $filter = null): ConstantScore
    {
        return new ConstantScore($filter);
    }

    /**
     * dis max query.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-dis-max-query.html
     *
     * @return DisMax
     */
    public function dis_max(): DisMax
    {
        return new DisMax();
    }

    /**
     * function score query.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-function-score-query.html
     *
     * @return FunctionScore
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
     * @param string $fieldName Field name
     * @param string $value     String to search for
     *
     * @return Fuzzy
     */
    public function fuzzy(string $fieldName = null, string $value = null): Fuzzy
    {
        return new Fuzzy($fieldName, $value);
    }

    /**
     * geo shape query.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-geo-shape-query.html
     */
    public function geo_shape()
    {
        throw new NotImplementedException();
    }

    /**
     * has child query.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-has-child-query.html
     *
     * @param string|BaseQuery|AbstractQuery $query
     * @param string                         $type  Parent document type
     *
     * @return HasChild
     */
    public function has_child($query, string $type = null): HasChild
    {
        return new HasChild($query, $type);
    }

    /**
     * has parent query.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-has-parent-query.html
     *
     * @param string|BaseQuery|AbstractQuery $query
     * @param string                         $type  Parent document type
     *
     * @return HasParent
     */
    public function has_parent($query, string $type): HasParent
    {
        return new HasParent($query, $type);
    }

    /**
     * ids query.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-ids-query.html
     *
     * @param array $ids
     *
     * @return Ids
     */
    public function ids(array $ids = []): Ids
    {
        return new Ids($ids);
    }

    /**
     * match all query.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-match-all-query.html
     *
     * @return MatchAll
     */
    public function match_all(): MatchAll
    {
        return new MatchAll();
    }

    /**
     * match none query.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-match-all-query.html#query-dsl-match-none-query
     *
     * @return MatchNone
     */
    public function match_none(): MatchNone
    {
        return new MatchNone();
    }

    /**
     * more like this query.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-mlt-query.html
     *
     * @return MoreLikeThis
     */
    public function more_like_this(): MoreLikeThis
    {
        return new MoreLikeThis();
    }

    /**
     * nested query.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-nested-query.html
     *
     * @return Nested
     */
    public function nested(): Nested
    {
        return new Nested();
    }

    /**
     * @param string     $type
     * @param int|string $id
     * @param bool       $ignoreUnmapped
     *
     * @return ParentId ParentId
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
     *
     * @return Prefix
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
     *
     * @return QueryString
     */
    public function query_string(string $queryString = ''): QueryString
    {
        return new QueryString($queryString);
    }

    /**
     * simple_query_string query.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-simple-query-string-query.html
     *
     * @param string $query
     * @param array  $fields
     *
     * @return SimpleQueryString
     */
    public function simple_query_string(string $query, array $fields = []): SimpleQueryString
    {
        return new SimpleQueryString($query, $fields);
    }

    /**
     * range query.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-range-query.html
     *
     * @param string $fieldName
     * @param array  $args
     *
     * @return Range
     */
    public function range(string $fieldName = null, array $args = []): Range
    {
        return new Range($fieldName, $args);
    }

    /**
     * regexp query.
     *
     * @param string $key
     * @param string $value
     * @param float  $boost
     *
     * @return Regexp
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-regexp-query.html
     */
    public function regexp(string $key = '', string $value = null, float $boost = 1.0): Regexp
    {
        return new Regexp($key, $value, $boost);
    }

    /**
     * span first query.
     *
     * @param AbstractQuery|array $match
     * @param int                 $end
     *
     * @return SpanFirst
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-span-first-query.html
     */
    public function span_first($match = null, int $end = null): SpanFirst
    {
        return new SpanFirst($match, $end);
    }

    /**
     * span multi term query.
     *
     * @param AbstractQuery|array $match
     *
     * @return SpanMulti
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
     * @param array $clauses
     * @param int   $slop
     * @param bool  $inOrder
     *
     * @return SpanNear
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
     * @param AbstractSpanQuery|null $include
     * @param AbstractSpanQuery|null $exclude
     *
     * @return SpanNot
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-span-not-query.html
     */
    public function span_not(AbstractSpanQuery $include = null, AbstractSpanQuery $exclude = null): SpanNot
    {
        return new SpanNot($include, $exclude);
    }

    /**
     * span_or query.
     *
     * @param array $clauses
     *
     * @return SpanOr
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
     * @param array $term
     *
     * @return SpanTerm
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
     * @param AbstractSpanQuery|null $little
     * @param AbstractSpanQuery|null $big
     *
     * @return SpanContaining
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-span-containing-query.html
     */
    public function span_containing(AbstractSpanQuery $little = null, AbstractSpanQuery $big = null): SpanContaining
    {
        return new SpanContaining($little, $big);
    }

    /**
     * span_within query.
     *
     * @param AbstractSpanQuery|null $little
     * @param AbstractSpanQuery|null $big
     *
     * @return SpanWithin
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-span-within-query.html
     */
    public function span_within(AbstractSpanQuery $little = null, AbstractSpanQuery $big = null): SpanWithin
    {
        return new SpanWithin($little, $big);
    }

    /**
     * term query.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-term-query.html
     *
     * @param array $term
     *
     * @return Term
     */
    public function term(array $term = []): Term
    {
        return new Term($term);
    }

    /**
     * terms query.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-terms-query.html
     *
     * @param string $key
     * @param array  $terms
     *
     * @return Terms
     */
    public function terms(string $key = '', array $terms = []): Terms
    {
        return new Terms($key, $terms);
    }

    /**
     * wildcard query.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-wildcard-query.html
     *
     * @param string $key   OPTIONAL Wildcard key
     * @param string $value OPTIONAL Wildcard value
     * @param float  $boost OPTIONAL Boost value (default = 1)
     *
     * @return Wildcard
     */
    public function wildcard(string $key = '', string $value = null, float $boost = 1.0): Wildcard
    {
        return new Wildcard($key, $value, $boost);
    }

    /**
     * geo distance query.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-geo-distance-query.html
     *
     * @param string       $key
     * @param array|string $location
     * @param string       $distance
     *
     * @return GeoDistance
     */
    public function geo_distance(string $key, $location, string $distance): GeoDistance
    {
        return new GeoDistance($key, $location, $distance);
    }

    /**
     * exists query.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-exists-query.html
     *
     * @param string $field
     *
     * @return Exists
     */
    public function exists(string $field): Exists
    {
        return new Exists($field);
    }

    /**
     * type query.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/5.0/query-dsl-percolate-query.html
     *
     * @return Percolate
     */
    public function percolate(): Percolate
    {
        return new Percolate();
    }
}
