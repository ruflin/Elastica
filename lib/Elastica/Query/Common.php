<?php
namespace Elastica\Query;

/**
 * Class Common.
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-common-terms-query.html
 */
class Common extends AbstractQuery
{
    const OPERATOR_AND = 'and';
    const OPERATOR_OR = 'or';

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
    public function __construct($field, $query, $cutoffFrequency)
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
    public function setField($field)
    {
        $this->_field = $field;

        return $this;
    }

    /**
     * Set the query string for this query.
     *
     * @param string $query
     *
     * @return $this
     */
    public function setQuery($query)
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
    public function setCutoffFrequency($frequency)
    {
        return $this->setQueryParam('cutoff_frequency', (float) $frequency);
    }

    /**
     * Set the logic operator for low frequency terms.
     *
     * @param string $operator see OPERATOR_* class constants for options
     *
     * @return $this
     */
    public function setLowFrequencyOperator($operator)
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
    public function setHighFrequencyOperator($operator)
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
     * @link Possible values for minimum_should_match https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-minimum-should-match.html
     */
    public function setMinimumShouldMatch($minimum)
    {
        return $this->setQueryParam('minimum_should_match', $minimum);
    }

    /**
     * Set the boost for this query.
     *
     * @param float $boost
     *
     * @return $this
     */
    public function setBoost($boost)
    {
        return $this->setQueryParam('boost', (float) $boost);
    }

    /**
     * Set the analyzer for this query.
     *
     * @param string $analyzer
     *
     * @return $this
     */
    public function setAnalyzer($analyzer)
    {
        return $this->setQueryParam('analyzer', $analyzer);
    }

    /**
     * Enable / disable computation of score factor based on the fraction of all query terms contained in the document.
     *
     * @param bool $disable disable_coord is false by default
     *
     * @return $this
     */
    public function setDisableCoord($disable = true)
    {
        return $this->setQueryParam('disable_coord', (bool) $disable);
    }

    /**
     * Set a parameter in the body of this query.
     *
     * @param string $key   parameter key
     * @param mixed  $value parameter value
     *
     * @return $this
     */
    public function setQueryParam($key, $value)
    {
        $this->_queryParams[$key] = $value;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $this->setParam($this->_field, $this->_queryParams);

        return parent::toArray();
    }
}
