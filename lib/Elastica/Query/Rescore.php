<?php

namespace Elastica\Query;

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
     * @param string|\Elastica\Query\AbstractQuery $rescore_query
     * @param string|\Elastica\Query\AbstractQuery $query
     */
    public function __construct($query = null, $rescore_query= null)
    {
        $this->setQuery($query);
        $this->setParam('rescore', array());
        $this->setRescoreQuery($rescore_query);
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
     * Sets rescore_query object
     *
     * @param  string|\Elastica\Query|\Elastica\Query\AbstractQuery $query
     * @return \Elastica\Query\Rescore
     */
    public function setRescoreQuery($rescore_query)
    {
        $query = BaseQuery::create($rescore_query);
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
        $rescore['query']['window_size'] = $size;

        return $this->setParam('rescore', $rescore);
    }

    /**
     * Sets query_weight
     *
     * @param int $weight
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
     * @param int $size
     * @return \Elastica\Query\Rescore
     */
    public function setRescoreQueryWeight($weight)
    {
        $rescore = $this->getParam('rescore');
        $rescore['query']['rescore_query_weight'] = $weight;

        return $this->setParam('rescore', $rescore);
    }
}