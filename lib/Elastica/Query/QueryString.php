<?php

namespace Elastica\Query;

/**
 * QueryString query.
 *
 * @author   Nicolas Ruflin <spam@ruflin.com>, Jasper van Wanrooy <jasper@vanwanrooy.net>
 *
 * @see     https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-query-string-query.html
 */
class QueryString extends AbstractQuery
{
    /**
     * Query string.
     *
     * @var string Query string
     */
    protected $_queryString;

    /**
     * Creates query string object. Calls setQuery with argument.
     *
     * @param string $queryString OPTIONAL Query string for object
     */
    public function __construct(string $queryString = '')
    {
        $this->setQuery($queryString);
    }

    /**
     * Sets a new query string for the object.
     *
     * @param string $query Query string
     *
     * @return $this
     */
    public function setQuery(string $query = ''): self
    {
        return $this->setParam('query', $query);
    }

    /**
     * Sets the default field.
     * You cannot set fields and default_field.
     *
     * If no field is set, _all is chosen
     *
     * @param string $field Field
     *
     * @return $this
     */
    public function setDefaultField(string $field): self
    {
        return $this->setParam('default_field', $field);
    }

    /**
     * Sets the default operator AND or OR.
     *
     * If no operator is set, OR is chosen
     *
     * @param string $operator Operator
     *
     * @return $this
     */
    public function setDefaultOperator(string $operator = 'or'): self
    {
        return $this->setParam('default_operator', $operator);
    }

    /**
     * Sets the analyzer to analyze the query with.
     *
     * @param string $analyzer Analyser to use
     *
     * @return $this
     */
    public function setAnalyzer(string $analyzer): self
    {
        return $this->setParam('analyzer', $analyzer);
    }

    /**
     * Sets the parameter to allow * and ? as first characters.
     *
     * If not set, defaults to true.
     *
     * @return $this
     */
    public function setAllowLeadingWildcard(bool $allow = true): self
    {
        return $this->setParam('allow_leading_wildcard', $allow);
    }

    /**
     * Sets the parameter to enable the position increments in result queries.
     *
     * If not set, defaults to true.
     *
     * @return $this
     */
    public function setEnablePositionIncrements(bool $enabled = true): self
    {
        return $this->setParam('enable_position_increments', $enabled);
    }

    /**
     * Sets the fuzzy prefix length parameter.
     *
     * If not set, defaults to 0.
     *
     * @return $this
     */
    public function setFuzzyPrefixLength(int $length = 0): self
    {
        return $this->setParam('fuzzy_prefix_length', $length);
    }

    /**
     * Sets the fuzzy minimal similarity parameter.
     *
     * If not set, defaults to 0.5
     *
     * @return $this
     */
    public function setFuzzyMinSim(float $minSim = 0.5): self
    {
        return $this->setParam('fuzzy_min_sim', $minSim);
    }

    /**
     * Sets the phrase slop.
     *
     * If zero, exact phrases are required.
     * If not set, defaults to zero.
     *
     * @return $this
     */
    public function setPhraseSlop(int $phraseSlop = 0): self
    {
        return $this->setParam('phrase_slop', $phraseSlop);
    }

    /**
     * Sets the boost value of the query.
     *
     * If not set, defaults to 1.0.
     *
     * @return $this
     */
    public function setBoost(float $boost = 1.0): self
    {
        return $this->setParam('boost', $boost);
    }

    /**
     * Allows analyzing of wildcard terms.
     *
     * If not set, defaults to true
     *
     * @return $this
     */
    public function setAnalyzeWildcard(bool $analyze = true): self
    {
        return $this->setParam('analyze_wildcard', $analyze);
    }

    /**
     * Sets the fields. If no fields are set, _all is chosen.
     * You cannot set fields and default_field.
     *
     * @param array $fields Fields
     *
     * @return $this
     */
    public function setFields(array $fields): self
    {
        return $this->setParam('fields', $fields);
    }

    /**
     * Whether to use bool or dis_max queries to internally combine results for multi field search.
     *
     * @param bool $value Determines whether to use
     *
     * @return $this
     */
    public function setUseDisMax(bool $value = true): self
    {
        return $this->setParam('use_dis_max', $value);
    }

    /**
     * When using dis_max, the disjunction max tie breaker.
     *
     * If not set, defaults to 0.0.
     *
     * @return $this
     */
    public function setTieBreaker(float $tieBreaker = 0.0): self
    {
        return $this->setParam('tie_breaker', $tieBreaker);
    }

    /**
     * Set a re-write condition. See https://github.com/elasticsearch/elasticsearch/issues/1186 for additional information.
     *
     * @return $this
     */
    public function setRewrite(string $rewrite = ''): self
    {
        return $this->setParam('rewrite', $rewrite);
    }

    /**
     * Set timezone option.
     *
     * @return $this
     */
    public function setTimezone(string $timezone): self
    {
        return $this->setParam('time_zone', $timezone);
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return ['query_string' => \array_merge(['query' => $this->_queryString], $this->getParams())];
    }
}
