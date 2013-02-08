<?php

namespace Elastica\Query;

use Elastica\Query as BaseQuery;
use Elastica\Script;

/**
 * Custom score query
 *
 * @category Xodoa
 * @package Elastica
 * @author Wu Yang <darkyoung@gmail.com>
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/custom-score-query.html
 */
class CustomScore extends AbstractQuery
{
    /**
     * Constructor
     *
     * @param string|array|\Elastica\Script        $script
     * @param string|\Elastica\Query\AbstractQuery $query
     */
    public function __construct($script = null, $query= null)
    {
        if ($script) {
            $this->setScript($script);
        }
        $this->setQuery($query);
    }

    /**
     * Sets query object
     *
     * @param  string|\Elastica\Query|\Elastica\Query\AbstractQuery $query
     * @return \Elastica\Query\CustomScore
     */
    public function setQuery($query)
    {
        $query = BaseQuery::create($query);
        $data = $query->toArray();

        return $this->setParam('query', $data['query']);
    }

    /**
     * Set script
     *
     * @param  string|\Elastica\Script          $script
     * @return \Elastica\Query\CustomScore
     */
    public function setScript($script)
    {
        $script = Script::create($script);
        foreach ($script->toArray() as $param => $value) {
            $this->setParam($param, $value);
        }

        return $this;
    }

    /**
     * Add params
     *
     * @param  array                           $params
     * @return \Elastica\Query\CustomScore
     */
    public function addParams(array $params)
    {
        $this->setParam('params', $params);

        return $this;
    }
}
