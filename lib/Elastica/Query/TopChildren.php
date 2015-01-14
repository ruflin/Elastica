<?php

namespace Elastica\Query;

use Elastica\Query as BaseQuery;

/**
 * Runs the child query with an estimated hits size, and out of the hit docs, aggregates it into parent docs.
 *
 * @category Xodoa
 * @package Elastica
 * @author Wu Yang <darkyoung@gmail.com>
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/top-children-query.html
 */
class TopChildren extends AbstractQuery
{
    /**
     * Construct topChildren query
     *
     * @param string|\Elastica\Query|\Elastica\Query\AbstractQuery $query
     * @param string                                               $type  Parent document type
     */
    public function __construct($query, $type = null)
    {
        $this->setQuery($query);
        $this->setType($type);
    }

    /**
     * Sets query object
     *
     * @param  string|\Elastica\Query|\Elastica\Query\AbstractQuery $query
     * @return \Elastica\Query\TopChildren
     */
    public function setQuery($query)
    {
        $query = BaseQuery::create($query);
        $data = $query->toArray();

        return $this->setParam('query', $data['query']);
    }

    /**
     * Set type of the parent document
     *
     * @param  string                      $type Parent document type
     * @return \Elastica\Query\TopChildren Current object
     */
    public function setType($type)
    {
        return $this->setParam('type', $type);
    }
}
