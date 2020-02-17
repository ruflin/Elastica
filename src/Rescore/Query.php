<?php

namespace Elastica\Rescore;

use Elastica\Query as BaseQuery;

/**
 * Query Rescore.
 *
 * @author Jason Hu <mjhu91@gmail.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-request-rescore.html
 */
class Query extends AbstractRescore
{
    /**
     * Constructor.
     *
     * @param \Elastica\Query\AbstractQuery|string $query
     */
    public function __construct($query = null)
    {
        $this->setParam('query', []);
        $this->setRescoreQuery($query);
    }

    /**
     * Override default implementation so params are in the format
     * expected by elasticsearch.
     *
     * @return array Rescore array
     */
    public function toArray(): array
    {
        $data = $this->getParams();

        if (!empty($this->_rawParams)) {
            $data = \array_merge($data, $this->_rawParams);
        }

        $array = $this->_convertArrayable($data);

        if (isset($array['query']['rescore_query']['query'])) {
            $array['query']['rescore_query'] = $array['query']['rescore_query']['query'];
        }

        return $array;
    }

    /**
     * Sets rescoreQuery object.
     *
     * @param \Elastica\Query|\Elastica\Query\AbstractQuery|string $rescoreQuery
     *
     * @return $this
     */
    public function setRescoreQuery($rescoreQuery): Query
    {
        $rescoreQuery = BaseQuery::create($rescoreQuery);

        $query = $this->getParam('query');
        $query['rescore_query'] = $rescoreQuery;

        return $this->setParam('query', $query);
    }

    /**
     * Sets query_weight.
     *
     * @return $this
     */
    public function setQueryWeight(float $weight): Query
    {
        $query = $this->getParam('query');
        $query['query_weight'] = $weight;

        return $this->setParam('query', $query);
    }

    /**
     * Sets rescore_query_weight.
     *
     * @return $this
     */
    public function setRescoreQueryWeight(float $weight): Query
    {
        $query = $this->getParam('query');
        $query['rescore_query_weight'] = $weight;

        return $this->setParam('query', $query);
    }
}
