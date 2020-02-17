<?php

namespace Elastica\Query;

/**
 * Class SimpleQueryString.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-simple-query-string-query.html
 */
class SimpleQueryString extends AbstractQuery
{
    public const OPERATOR_AND = 'and';
    public const OPERATOR_OR = 'or';

    public function __construct(string $query, array $fields = [])
    {
        $this->setQuery($query);
        if (0 < \count($fields)) {
            $this->setFields($fields);
        }
    }

    /**
     * Set the querystring for this query.
     *
     * @param string $query see ES documentation for querystring syntax
     *
     * @return $this
     */
    public function setQuery(string $query): self
    {
        return $this->setParam('query', $query);
    }

    /**
     * @param string[] $fields the fields on which to perform this query. Defaults to index.query.default_field.
     *
     * @return $this
     */
    public function setFields(array $fields): self
    {
        return $this->setParam('fields', $fields);
    }

    /**
     * Set the default operator to use if no explicit operator is defined in the query string.
     *
     * @param string $operator see OPERATOR_* constants for options
     *
     * @return $this
     */
    public function setDefaultOperator(string $operator = self::OPERATOR_OR): self
    {
        return $this->setParam('default_operator', $operator);
    }

    /**
     * Set the analyzer used to analyze each term of the query.
     *
     * @return $this
     */
    public function setAnalyzer(string $analyzer): self
    {
        return $this->setParam('analyzer', $analyzer);
    }

    /**
     * Set minimum_should_match option.
     *
     * @param int|string $minimumShouldMatch
     *
     * @return $this
     */
    public function setMinimumShouldMatch($minimumShouldMatch): self
    {
        return $this->setParam('minimum_should_match', $minimumShouldMatch);
    }
}
