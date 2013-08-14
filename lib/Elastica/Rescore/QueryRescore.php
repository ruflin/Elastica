<?php

namespace Elastica\Rescore;

use Elastica\Query as BaseQuery;

/**
 * Rescore query
 *
 * @category Xodoa
 * @package Elastica
 * @author Jason Hu <mjhu91@gmail.com>
 * @link http://www.elasticsearch.org/guide/reference/api/search/rescore/
 */
class QueryRescore extends AbstractRescore
{

    /**
     * Gets the query param
     *
     * @return array
     */
    protected function getQueryParam()
    {
        if (!$this->hasParam('query'))
        {
            $this->setParam('query', array());
        }

        return $this->getParam('query');
    }
    
    /**
     * Sets query object
     *
     * @param  string|\Elastica\Query|\Elastica\Query\AbstractQuery $query
     * @return \Elastica\Rescore
     */
    public function setQuery($rescoreQuery)
    {
        $query = BaseQuery::create($rescoreQuery);
        $data = $query->toArray();
        $query = $this->getQueryParam();

        $query['rescore_query'] = $data['query'];

        return $this->setParam('query', $query);
    }

    /**
     * Sets query_weight
     *
     * @param float $weight
     * @return \Elastica\Rescore
     */
    public function setQueryWeight($weight)
    {
        $query = $this->getQueryParam();
        $query['query_weight'] = $weight;

        return $this->setParam('query', $query);
    }

    /**
     * Sets rescore_query_weight
     *
     * @param float $size
     * @return \Elastica\Rescore
     */
    public function setRescoreQueryWeight($weight)
    {
        $query = $this->getQueryParam();
        $query['rescore_query_weight'] = $weight;

        return $this->setParam('query', $query);
    }
}