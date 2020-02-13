<?php

namespace Elastica\Query;

/**
 * Class Common.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-common-terms-query.html
 */
class Common extends AbstractQuery
{
    public const OPERATOR_AND = 'and';
    public const OPERATOR_OR = 'or';

    /**
     * @var string
     */
    protected $_field;

    /**
     * @var array
     */
    protected $_queryParams = [];

    /**
     * @param string $field           the field on which to query
     * @param string $query           the query string
     * @param float  $cutoffFrequency percentage in decimal form (.001 == 0.1%)
     */
    public function __construct(string $field, string $query, float $cutoffFrequency)
    {
        $this->setField($field);
        $this->setQuery($query);
        $this->setCutoffFrequency($cutoffFrequency);
    }

    /**
     * Set the field on which to query.
     *
     * @param string $field the field on which to query
     *
     * @return $this
     */
    public function setField(string $field): self
    {
        $this->_field = $field;

        return $this;
    }

    /**
     * Set the query string for this query.
     *
     * @return $this
     */
    public function setQuery(string $query): self
    {
        return $this->setQueryParam('query', $query);
    }

    /**
     * Set the frequency below which terms will be put in the low frequency group.
     *
     * @param float $frequency percentage in decimal form (.001 == 0.1%)
     *
     * @return $this
     */
    public function setCutoffFrequency(float $frequency): self
    {
        return $this->setQueryParam('cutoff_frequency', $frequency);
    }

    /**
     * Set the logic operator for low frequency terms.
     *
     * @param string $operator see OPERATOR_* class constants for options
     *
     * @return $this
     */
    public function setLowFrequencyOperator(string $operator = self::OPERATOR_OR): self
    {
        return $this->setQueryParam('low_freq_operator', $operator);
    }

    /**
     * Set the logic operator for high frequency terms.
     *
     * @param string $operator see OPERATOR_* class constants for options
     *
     * @return $this
     */
    public function setHighFrequencyOperator(string $operator = self::OPERATOR_OR): self
    {
        return $this->setQueryParam('high_frequency_operator', $operator);
    }

    /**
     * Set the minimum_should_match parameter.
     *
     * @param int|string $minimum minimum number of low frequency terms which must be present
     *
     * @return $this
     *
     * @see Possible values for minimum_should_match https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-minimum-should-match.html
     */
    public function setMinimumShouldMatch($minimum): self
    {
        return $this->setQueryParam('minimum_should_match', $minimum);
    }

    /**
     * Set the boost for this query.
     *
     * @return $this
     */
    public function setBoost(float $boost): self
    {
        return $this->setQueryParam('boost', $boost);
    }

    /**
     * Set the analyzer for this query.
     *
     * @return $this
     */
    public function setAnalyzer(string $analyzer): self
    {
        return $this->setQueryParam('analyzer', $analyzer);
    }

    /**
     * Set a parameter in the body of this query.
     *
     * @param string $key   parameter key
     * @param mixed  $value parameter value
     *
     * @return $this
     */
    public function setQueryParam(string $key, $value): self
    {
        $this->_queryParams[$key] = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        $this->setParam($this->_field, $this->_queryParams);

        return parent::toArray();
    }
}
