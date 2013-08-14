<?php

namespace Elastica\Query;

use Elastica\Query as BaseQuery;

/**
 * Rescore query
 *
 * @category Xodoa
 * @package Elastica
 * @author Jason Hu <mjhu91@gmail.com>
 * @link http://www.elasticsearch.org/guide/reference/api/search/rescore/
 */
class Rescore extends AbstractQuery
{
    /**
     * Constructor
     *
     * @param string|\Elastica\Query\AbstractQuery $rescoreQuery
     * @param string|\Elastica\Query\AbstractQuery $query
     */
    public function __construct($query = null, $rescoreQuery= null)
    {
        $this->setQuery($query);
        $this->setParam('rescore', array());
        $this->setRescoreQuery($rescoreQuery);
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
     * Sets query object
     *
     * @param  string|\Elastica\Query|\Elastica\Query\AbstractQuery $query
     * @return \Elastica\Query\Rescore
     */
    public function setQuery($query)
    {
        $query = BaseQuery::create($query);
        $data = $query->toArray();

        return $this->setParam('query', $data['query']);
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

        $rescore = $this->getParam('rescore');
        $rescore['query']['rescore_query'] = $data['query'];

        return $this->setParam('rescore', $rescore);
    }

    /**
     * Sets window_size
     *
     * @param int $size
     * @return \Elastica\Query\Rescore
     */
    public function setWindowSize($size)
    {
        $rescore = $this->getParam('rescore');
        $rescore['window_size'] = $size;

        return $this->setParam('rescore', $rescore);
    }

    /**
     * Sets query_weight
     *
     * @param float $weight
     * @return \Elastica\Query\Rescore
     */
    public function setQueryWeight($weight)
    {
        $rescore = $this->getParam('rescore');
        $rescore['query']['query_weight'] = $weight;

        return $this->setParam('rescore', $rescore);
    }

    /**
     * Sets rescore_query_weight
     *
     * @param float $size
     * @return \Elastica\Query\Rescore
     */
    public function setRescoreQueryWeight($weight)
    {
        $rescore = $this->getParam('rescore');
        $rescore['query']['rescore_query_weight'] = $weight;

        return $this->setParam('rescore', $rescore);
    }
}