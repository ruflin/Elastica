<?php

namespace Elastica\Query;
use Elastica\Query as BaseQuery;

/**
 * Returns parent documents having child docs matching the query
 *
 * @category Xodoa
 * @package Elastica
 * @author Fabian Vogler <fabian@equivalence.ch>
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/has-child-query.html
 */
class HasChild extends AbstractQuery
{
    /**
     * Construct HasChild Query
     *
     * @param string|\Elastica\Query|\Elastica\Query\AbstractQuery $query
     * @param string                $type  Parent document type
     */
    public function __construct($query, $type = null)
    {
        $this->setType($type);
        $this->setQuery($query);
    }

    /**
     * Sets query object
     *
     * @param  string|\Elastica\Query|\Elastica\Query\AbstractQuery $query
     * @return \Elastica\Query\HasChild
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
     * @param  string                       $type Parent document type
     * @return \Elastica\Query\HasChild Current object
     */
    public function setType($type)
    {
        return $this->setParam('type', $type);
    }

    /**
     * Sets the scope
     *
     * @param  string                       $scope Scope
     * @return \Elastica\Query\HasChild Current object
     */
    public function setScope($scope)
    {
        return $this->setParam('_scope', $scope);
    }
}
