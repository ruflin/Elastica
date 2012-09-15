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
     * @param  string                     $script
     * @return Elastica_Query_CustomScore
     */
    public function setScript($script)
    {
        return $this->setParam('script', $script);
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
