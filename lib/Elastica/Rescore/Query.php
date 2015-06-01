<?php
namespace Elastica\Rescore;

use Elastica\Query as BaseQuery;

/**
 * Query Rescore.
 *
 * @author Jason Hu <mjhu91@gmail.com>
 *
 * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/search-request-rescore.html
 */
class Query extends AbstractRescore
{
    /**
     * Constructor.
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
     * expected by elasticsearch.
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
     * Sets rescoreQuery object.
     *
     * @param string|\Elastica\Query|\Elastica\Query\AbstractQuery $query
     *
     * @return $this
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
     * Sets query_weight.
     *
     * @param float $weight
     *
     * @return $this
     */
    public function setQueryWeight($weight)
    {
        $query = $this->getParam('query');
        $query['query_weight'] = $weight;

        return $this->setParam('query', $query);
    }

    /**
     * Sets rescore_query_weight.
     *
     * @param float $size
     *
     * @return $this
     */
    public function setRescoreQueryWeight($weight)
    {
        $query = $this->getParam('query');
        $query['rescore_query_weight'] = $weight;

        return $this->setParam('query', $query);
    }
}
