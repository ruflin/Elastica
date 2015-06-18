<?php
namespace Elastica\QueryBuilder\DSL;

use Elastica\Exception\NotImplementedException;
use Elastica\Filter\AbstractFilter;
use Elastica\Query\AbstractQuery;
use Elastica\Query\BoolQuery;
use Elastica\Query\Boosting;
use Elastica\Query\Common;
use Elastica\Query\ConstantScore;
use Elastica\Query\DisMax;
use Elastica\Query\Filtered;
use Elastica\Query\FunctionScore;
use Elastica\Query\Fuzzy;
use Elastica\Query\FuzzyLikeThis;
use Elastica\Query\HasChild;
use Elastica\Query\HasParent;
use Elastica\Query\Ids;
use Elastica\Query\Match;
use Elastica\Query\MatchAll;
use Elastica\Query\MoreLikeThis;
use Elastica\Query\MultiMatch;
use Elastica\Query\Nested;
use Elastica\Query\Prefix;
use Elastica\Query\QueryString;
use Elastica\Query\Range;
use Elastica\Query\Regexp;
use Elastica\Query\SimpleQueryString;
use Elastica\Query\Term;
use Elastica\Query\Terms;
use Elastica\Query\TopChildren;
use Elastica\Query\Wildcard;
use Elastica\QueryBuilder\DSL;

/**
 * elasticsearch query DSL.
 *
 * @author Manuel Andreo Garcia <andreo.garcia@googlemail.com>
 *
 * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-queries.html
 */
class Query implements DSL
{
    /**
     * must return type for QueryBuilder usage.
     *
     * @return string
     */
    public function getType()
    {
        return self::TYPE_QUERY;
    }

    /**
     * match query.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-match-query.html
     *
     * @param string $field
     * @param mixed  $values
     *
     * @return Match
     */
    public function match($field = null, $values = null)
    {
        return new Match($field, $values);
    }

    /**
     * multi match query.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-multi-match-query.html
     *
     * @return \Elastica\Query\MultiMatch
     */
    public function multi_match()
    {
        return new MultiMatch();
    }

    /**
     * bool query.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-bool-query.html
     *
     * @return \Elastica\Query\BoolQuery
     */
    public function bool()
    {
        return new BoolQuery();
    }

    /**
     * boosting query.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-boosting-query.html
     *
     * @return Boosting
     */
    public function boosting()
    {
        return new Boosting();
    }

    /**
     * common terms query.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-common-terms-query.html
     *
     * @param string $field
     * @param string $query
     * @param float  $cutoffFrequency percentage in decimal form (.001 == 0.1%)
     *
     * @return Common
     */
    public function common_terms($field, $query, $cutoffFrequency)
    {
        return new Common($field, $query, $cutoffFrequency);
    }

    /**
     * custom filters score query.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/0.90/query-dsl-custom-filters-score-query.html
     */
    public function custom_filters_score()
    {
        throw new NotImplementedException();
    }

    /**
     * custom score query.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/0.90/query-dsl-custom-score-query.html
     */
    public function custom_score()
    {
        throw new NotImplementedException();
    }

    /**
     * custom boost factor query.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/0.90/query-dsl-custom-boost-factor-query.html
     */
    public function custom_boost_factor()
    {
        throw new NotImplementedException();
    }

    /**
     * constant score query.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-constant-score-query.html
     *
     * @param null|\Elastica\Filter\AbstractFilter|array $filter
     *
     * @return ConstantScore
     */
    public function constant_score($filter = null)
    {
        return new ConstantScore($filter);
    }

    /**
     * dis max query.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-dis-max-query.html
     *
     * @return DisMax
     */
    public function dis_max()
    {
        return new DisMax();
    }

    /**
     * field query.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/0.90/query-dsl-field-query.html
     */
    public function field()
    {
        throw new NotImplementedException();
    }

    /**
     * filtered query.
     *
     * @param AbstractFilter $filter
     * @param AbstractQuery  $query
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-filtered-query.html
     *
     * @return Filtered
     */
    public function filtered(AbstractQuery $query = null, AbstractFilter $filter = null)
    {
        return new Filtered($query, $filter);
    }

    /**
     * fuzzy like this query.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-flt-query.html
     *
     * @return FuzzyLikeThis
     */
    public function fuzzy_like_this()
    {
        return new FuzzyLikeThis();
    }

    /**
     * fuzzy like this field query.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-flt-field-query.html
     */
    public function fuzzy_like_this_field()
    {
        throw new NotImplementedException();
    }

    /**
     * function score query.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-function-score-query.html
     *
     * @return FunctionScore
     */
    public function function_score()
    {
        return new FunctionScore();
    }

    /**
     * fuzzy query.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-fuzzy-query.html
     *
     * @param string $fieldName Field name
     * @param string $value     String to search for
     *
     * @return Fuzzy
     */
    public function fuzzy($fieldName = null, $value = null)
    {
        return new Fuzzy($fieldName, $value);
    }

    /**
     * geo shape query.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-geo-shape-query.html
     */
    public function geo_shape()
    {
        throw new NotImplementedException();
    }

    /**
     * has child query.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-has-child-query.html
     *
     * @param string|\Elastica\Query|\Elastica\Query\AbstractQuery $query
     * @param string                                               $type  Parent document type
     *
     * @return HasChild
     */
    public function has_child($query, $type = null)
    {
        return new HasChild($query, $type);
    }

    /**
     * has parent query.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-has-parent-query.html
     *
     * @param string|\Elastica\Query|\Elastica\Query\AbstractQuery $query
     * @param string                                               $type  Parent document type
     *
     * @return HasParent
     */
    public function has_parent($query, $type)
    {
        return new HasParent($query, $type);
    }

    /**
     * ids query.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-ids-query.html
     *
     * @param array|string|\Elastica\Type $type
     * @param array                       $ids
     *
     * @return Ids
     */
    public function ids($type = null, array $ids = array())
    {
        return new Ids($type, $ids);
    }

    /**
     * indices query.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-indices-query.html
     */
    public function indices()
    {
        throw new NotImplementedException();
    }

    /**
     * match all query.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-match-all-query.html
     *
     * @return MatchAll
     */
    public function match_all()
    {
        return new MatchAll();
    }

    /**
     * more like this query.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-mlt-query.html
     *
     * @return MoreLikeThis
     */
    public function more_like_this()
    {
        return new MoreLikeThis();
    }

    /**
     * more_like_this_field query.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/1.4/query-dsl-mlt-field-query.html
     * @deprecated More Like This Field query is deprecated as of ES 1.4 and will be removed in ES 2.0
     */
    public function more_like_this_field()
    {
        throw new NotImplementedException();
    }

    /**
     * nested query.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-nested-query.html
     *
     * @return Nested
     */
    public function nested()
    {
        return new Nested();
    }

    /**
     * prefix query.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-prefix-query.html
     *
     * @param array $prefix Prefix array
     *
     * @return Prefix
     */
    public function prefix(array $prefix = array())
    {
        return new Prefix($prefix);
    }

    /**
     * query string query.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-query-string-query.html
     *
     * @param string $queryString OPTIONAL Query string for object
     *
     * @return QueryString
     */
    public function query_string($queryString = '')
    {
        return new QueryString($queryString);
    }

    /**
     * simple_query_string query.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-simple-query-string-query.html
     *
     * @param string $query
     * @param array  $fields
     *
     * @return SimpleQueryString
     */
    public function simple_query_string($query, array $fields = array())
    {
        return new SimpleQueryString($query, $fields);
    }

    /**
     * range query.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-range-query.html
     *
     * @param string $fieldName
     * @param array  $args
     *
     * @return Range
     */
    public function range($fieldName = null, array $args = array())
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
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-regexp-query.html
     */
    public function regexp($key = '', $value = null, $boost = 1.0)
    {
        return new Regexp($key, $value, $boost);
    }

    /**
     * span first query.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-span-first-query.html
     */
    public function span_first()
    {
        throw new NotImplementedException();
    }

    /**
     * span multi term query.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-span-multi-term-query.html
     */
    public function span_multi_term()
    {
        throw new NotImplementedException();
    }

    /**
     * span near query.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-span-near-query.html
     */
    public function span_near()
    {
        throw new NotImplementedException();
    }

    /**
     * span not query.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-span-not-query.html
     */
    public function span_not()
    {
        throw new NotImplementedException();
    }

    /**
     * span or query.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-span-or-query.html
     */
    public function span_or()
    {
        throw new NotImplementedException();
    }

    /**
     * span term query.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-span-term-query.html
     */
    public function span_term()
    {
        throw new NotImplementedException();
    }

    /**
     * term query.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-term-query.html
     *
     * @param array $term
     *
     * @return Term
     */
    public function term(array $term = array())
    {
        return new Term($term);
    }

    /**
     * terms query.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-terms-query.html
     *
     * @param string $key
     * @param array  $terms
     *
     * @return Terms
     */
    public function terms($key = '', array $terms = array())
    {
        return new Terms($key, $terms);
    }

    /**
     * top children query.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-top-children-query.html
     *
     * @param string|AbstractQuery|\Elastica\Query $query
     * @param string                               $type
     *
     * @return TopChildren
     */
    public function top_children($query, $type = null)
    {
        return new TopChildren($query, $type);
    }

    /**
     * wildcard query.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-wildcard-query.html
     *
     * @param string $key   OPTIONAL Wildcard key
     * @param string $value OPTIONAL Wildcard value
     * @param float  $boost OPTIONAL Boost value (default = 1)
     *
     * @return Wildcard
     */
    public function wildcard($key = '', $value = null, $boost = 1.0)
    {
        return new Wildcard($key, $value, $boost);
    }

    /**
     * text query.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/0.90/query-dsl-text-query.html
     */
    public function text()
    {
        throw new NotImplementedException();
    }

    /**
     * minimum should match query.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-minimum-should-match.html
     */
    public function minimum_should_match()
    {
        throw new NotImplementedException();
    }

    /**
     * template query.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-template-query.html
     */
    public function template()
    {
        throw new NotImplementedException();
    }
}
