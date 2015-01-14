<?php

namespace Elastica\Rescore;

use Elastica\Query as BaseQuery;

/**
 * Query Rescore
 *
 * @category Xodoa
 * @package Elastica
 * @author Jason Hu <mjhu91@gmail.com>
 * @link http://www.elasticsearch.org/guide/reference/api/search/rescore/
 */
class Query extends AbstractRescore
{
    /**
     * Constructor
     *
     * @param string|\Elastica\Query\AbstractQuery $rescoreQuery
     * @param string|\Elastica\Query\AbstractQuery $query
     */
    public function __construct($query = null)
    {
        $this->setParam('query', array());
        $this->setRescoreQuery($query);
    }

    /**
     * Override default implementation so params are in the format
     * expected by elasticsearch
     *
     * @return array Rescore array
     */
    public function toArray()
    {
        $data = $this->getParams();

        if (!empty($this->_rawParams)) {
            $data = array_merge($data, $this->_rawParams);
        }

        return $data;
    }

    /**
     * Sets rescoreQuery object
     *
     * @param  string|\Elastica\Query|\Elastica\Query\AbstractQuery $query
     * @return \Elastica\Query\Rescore
     */
    public function setRescoreQuery($rescoreQuery)
    {
        $query = BaseQuery::create($rescoreQuery);
        $data = $query->toArray();

        $query = $this->getParam('query');
        $query['rescore_query'] = $data['query'];

        return $this->setParam('query', $query);
    }

    /**
     * Sets query_weight
     *
     * @param  float                   $weight
     * @return \Elastica\Query\Rescore
     */
    public function setQueryWeight($weight)
    {
        $query = $this->getParam('query');
        $query['query_weight'] = $weight;

        return $this->setParam('query', $query);
    }

    /**
     * Sets rescore_query_weight
     *
     * @param  float                   $size
     * @return \Elastica\Query\Rescore
     */
    public function setRescoreQueryWeight($weight)
    {
        $query = $this->getParam('query');
        $query['rescore_query_weight'] = $weight;

        return $this->setParam('query', $query);
    }
}
