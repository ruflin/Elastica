<?php

declare(strict_types=1);

namespace Elastica\Query;

/**
 * Combined fields query.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-combined-fields-query.html
 */
class CombinedFields extends AbstractQuery
{
    public const OPERATOR_OR = 'or';
    public const OPERATOR_AND = 'and';

    public const ZERO_TERM_NONE = 'none';
    public const ZERO_TERM_ALL = 'all';

    /**
     * Sets the query.
     *
     * @param string $query Query
     *
     * @return $this
     */
    public function setQuery(string $query = ''): self
    {
        return $this->setParam('query', $query);
    }

    /**
     * Sets Fields to be used in the query.
     *
     * @param array $fields Fields
     *
     * @return $this
     */
    public function setFields(array $fields = []): self
    {
        return $this->setParam('fields', $fields);
    }

    /**
     * Sets operator for Match Query.
     *
     * If not set, defaults to 'or'
     *
     * @return $this
     */
    public function setOperator(string $operator = self::OPERATOR_OR): self
    {
        return $this->setParam('operator', $operator);
    }

    /**
     * Set field minimum should match for Match Query.
     *
     * @return $this
     */
    public function setMinimumShouldMatch($minimumShouldMatch): self
    {
        return $this->setParam('minimum_should_match', $minimumShouldMatch);
    }

    /**
     * Set zero terms query for Match Query.
     *
     * If not set, default to 'none'
     *
     * @return $this
     */
    public function setZeroTermsQuery(string $zeroTermQuery = self::ZERO_TERM_NONE): self
    {
        return $this->setParam('zero_terms_query', $zeroTermQuery);
    }
}
