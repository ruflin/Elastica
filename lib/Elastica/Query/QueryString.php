<?php
/**
 * QueryString query
 *
 * @uses     Elastica_Query_Abstract
 * @category Xodoa
 * @package  Elastica
 * @author   Nicolas Ruflin <spam@ruflin.com>, Jasper van Wanrooy <jasper@vanwanrooy.net>
 * @link     http://www.elasticsearch.org/guide/reference/query-dsl/query-string-query.html
 */
class Elastica_Query_QueryString extends Elastica_Query_Abstract
{
    /**
     * Query string
     *
     * @var string Query string
     */
    protected $_queryString = '';

    /**
     * Creates query string object. Calls setQuery with argument
     *
     * @param string $queryString OPTIONAL Query string for object
     */
    public function __construct($queryString = '')
    {
        $this->setQuery($queryString);
    }

    /**
     * Sets a new query string for the object
     *
     * @param  string                     $query Query string
     * @throws Elastica_Exception_Invalid
     * @return Elastica_Query_QueryString Current object
     */
    public function setQuery($query = '')
    {
        if (!is_string($query)) {
            throw new Elastica_Exception_Invalid('Parameter has to be a string');
        }

        return $this->setParam('query', $query);
    }

    /**
     * Sets the default operator AND or OR
     *
     * If no operator is set, OR is chosen
     *
     * @param  string                     $queryString Query string
     * @return Elastica_Query_QueryString Current object
     * @deprecated Please use setQuery instead
     */
    public function setQueryString($queryString)
    {
        return $this->setQuery($queryString);
    }

    /**
     * Sets the default field
     *
     * If no field is set, _all is chosen
     *
     * @param  string                     $field Field
     * @return Elastica_Query_QueryString Current object
     */
    public function setDefaultField($field)
    {
        return $this->setParam('default_field', $field);
    }

    /**
     * Sets the default operator AND or OR
     *
     * If no operator is set, OR is chosen
     *
     * @param  string                     $operator Operator
     * @return Elastica_Query_QueryString Current object
     */
    public function setDefaultOperator($operator)
    {
        return $this->setParam('default_operator', $operator);
    }

    /**
     * Sets the analyzer to analyze the query with.
     *
     * @param  string                     $analyzer Analyser to use
     * @return Elastica_Query_QueryString Current object
     */
    public function setAnalyzer($analyzer)
    {
        return $this->setParam('analyzer', $analyzer);
    }

    /**
     * Sets the parameter to allow * and ? as first characters.
     *
     * If not set, defaults to true.
     *
     * @param  bool                       $allow
     * @return Elastica_Query_QueryString Current object
     */
    public function setAllowLeadingWildcard($allow = true)
    {
        return $this->setParam('allow_leading_wildcard', (bool) $allow);
    }

    /**
     * Sets the parameter to auto-lowercase terms of some queries.
     *
     * If not set, defaults to true.
     *
     * @param  bool                       $lowercase
     * @return Elastica_Query_QueryString Current object
     */
    public function setLowercaseExpandedTerms($lowercase = true)
    {
        return $this->setParam('lowercase_expanded_terms', (bool) $lowercase);
    }

    /**
     * Sets the parameter to enable the position increments in result queries.
     *
     * If not set, defaults to true.
     *
     * @param  bool                       $enabled
     * @return Elastica_Query_QueryString Current object
     */
    public function setEnablePositionIncrements($enabled = true)
    {
        return $this->setParam('enable_position_increments', (bool) $enabled);
    }

    /**
     * Sets the fuzzy prefix length parameter.
     *
     * If not set, defaults to 0.
     *
     * @param  int                        $length
     * @return Elastica_Query_QueryString Current object
     */
    public function setFuzzyPrefixLength($length = 0)
    {
        return $this->setParam('fuzzy_prefix_length', (int) $length);
    }

    /**
     * Sets the fuzzy minimal similarity parameter.
     *
     * If not set, defaults to 0.5
     *
     * @param  float                      $minSim
     * @return Elastica_Query_QueryString Current object
     */
    public function setFuzzyMinSim($minSim = 0.5)
    {
        return $this->setParam('fuzzy_min_sim', (float) $minSim);
    }

    /**
     * Sets the phrase slop.
     *
     * If zero, exact phrases are required.
     * If not set, defaults to zero.
     *
     * @param  int                        $phraseSlop
     * @return Elastica_Query_QueryString Current object
     */
    public function setPhraseSlop($phraseSlop = 0)
    {
        return $this->setParam('phrase_slop', (int) $phraseSlop);
    }

    /**
     * Sets the boost value of the query.
     *
     * If not set, defaults to 1.0.
     *
     * @param  float                      $boost
     * @return Elastica_Query_QueryString Current object
     */
    public function setBoost($boost = 1.0)
    {
        return $this->setParam('boost', (float) $boost);
    }

    /**
     * Allows analyzing of wildcard terms.
     *
     * If not set, defaults to false
     *
     * @param  bool                       $analyze
     * @return Elastica_Query_QueryString Current object
     */
    public function setAnalyzeWildcard($analyze = true)
    {
        return $this->setParam('analyze_wildcard', (bool) $analyze);
    }

    /**
     * Sets the param to automatically generate phrase queries.
     *
     * If not set, defaults to false.
     *
     * @param  bool                       $autoGenerate
     * @return Elastica_Query_QueryString Current object
     */
    public function setAutoGeneratePhraseQueries($autoGenerate = true)
    {
        return $this->setParam('auto_generate_phrase_queries', (bool) $autoGenerate);
    }

    /**
     * Sets the fields
     *
     * If no fields are set, _all is chosen
     *
     * @param  array                      $fields Fields
     * @throws Elastica_Exception_Invalid
     * @return Elastica_Query_QueryString Current object
     */
    public function setFields(array $fields)
    {
        if (!is_array($fields)) {
            throw new Elastica_Exception_Invalid('Parameter has to be an array');
        }

        return $this->setParam('fields', $fields);
    }

    /**
     * Whether to use bool or dis_max queries to internally combine results for multi field search.
     *
     * @param  bool                       $value Determines whether to use
     * @return Elastica_Query_QueryString Current object
     */
    public function setUseDisMax($value = true)
    {
        return $this->setParam('use_dis_max', (bool) $value);
    }

    /**
     * When using dis_max, the disjunction max tie breaker.
     *
     * If not set, defaults to 0.
     *
     * @param  int                        $tieBreaker
     * @return Elastica_Query_QueryString Current object
     * @deprecated
     */
    public function setTieBraker($tieBreaker = 0)
    {
        return $this->setTieBreaker($tieBreaker);
    }

    /**
     * When using dis_max, the disjunction max tie breaker.
     *
     * If not set, defaults to 0.
     *
     * @param  int                        $tieBreaker
     * @return Elastica_Query_QueryString Current object
     */
    public function setTieBreaker($tieBreaker = 0)
    {
        return $this->setParam('tie_breaker', (int) $tieBreaker);
    }

    /**
     * Set a re-write condition. See https://github.com/elasticsearch/elasticsearch/issues/1186 for additional information
     *
     * @param  string         $rewrite
     * @return Elastica_Param
     */
    public function setRewrite($rewrite = "")
    {
        return $this->setParam('rewrite', $rewrite);
    }

    /**
     * Converts query to array
     *
     * @see Elastica_Param::toArray()
     * @return array Query array
     */
    public function toArray()
    {
        return array('query_string' => array_merge(array('query' => $this->_queryString), $this->getParams()),);
    }
}
