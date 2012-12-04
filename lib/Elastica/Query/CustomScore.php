<?php

/**
 * Custom score query
 *
 * @uses Elastica_Query_Abstract
 * @category Xodoa
 * @package Elastica
 * @author Wu Yang <darkyoung@gmail.com>
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/custom-score-query.html
 */
class Elastica_Query_CustomScore extends Elastica_Query_Abstract
{
    /**
     * Constructor
     *
     * @param string|array|Elastica_Script $script
     * @param string|Elastica_Query_Abstract $query
     */
    public function __construct($script = null, $query= null)
    {
        if ($script) {
            $this->setScript($script);
        }
        if ($query) {
            $this->setQuery($query);
        }
    }

    /**
     * Sets query object
     *
     * @param  string|Elastica_Query|Elastica_Query_Abstract $query
     * @return Elastica_Query_CustomScore
     */
    public function setQuery($query)
    {
        $query = Elastica_Query::create($query);
        $data = $query->toArray();

        return $this->setParam('query', $data['query']);
    }

    /**
     * Set script
     *
     * @param  string|Elastica_Script $script
     * @return Elastica_Query_CustomScore
     */
    public function setScript($script)
    {
        $script = Elastica_Script::create($script);
        foreach ($script->toArray() as $param => $value) {
            $this->setParam($param, $value);
        }
        return $this;
    }

    /**
     * Add params
     *
     * @param  array                      $params
     * @return Elastica_Query_CustomScore
     */
    public function addParams(array $params)
    {
        $this->setParam('params', $params);

        return $this;
    }
}
