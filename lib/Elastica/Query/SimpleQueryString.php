<?php

namespace Elastica\Query;

/**
 * Class SimpleQueryString
 * @package Elastica\Query
 * @link http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/query-dsl-simple-query-string-query.html
 */
class SimpleQueryString extends AbstractQuery
{
    const OPERATOR_AND = "and";
    const OPERATOR_OR = "or";

    /**
     * @param string $query
     * @param array $fields
     */
    public function __construct($query, array $fields = array())
    {
        $this->setQuery($query);
        if (sizeof($fields)) {
            $this->setFields($fields);
        }
    }

    /**
     * Set the querystring for this query
     * @param string $query see ES documentation for querystring syntax
     * @return \Elastica\Query\SimpleQueryString
     */
    public function setQuery($query)
    {
        return $this->setParam("query", $query);
    }

    /**
     * @param string[] $fields the fields on which to perform this query. Defaults to index.query.default_field.
     * @return \Elastica\Query\SimpleQueryString
     */
    public function setFields(array $fields)
    {
        return $this->setParam("fields", $fields);
    }

    /**
     * Set the default operator to use if no explicit operator is defined in the query string
     * @param string $operator see OPERATOR_* constants for options
     * @return \Elastica\Query\SimpleQueryString
     */
    public function setDefaultOperator($operator)
    {
        return $this->setParam("default_operator", $operator);
    }

    /**
     * Set the analyzer used to analyze each term of the query
     * @param string $analyzer
     * @return \Elastica\Query\SimpleQueryString
     */
    public function setAnalyzer($analyzer)
    {
        return $this->setParam("analyzer", $analyzer);
    }
} 