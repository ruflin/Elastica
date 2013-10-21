<?php

namespace Elastica\Filter;

/**
 * Returns parent documents having child docs matching the query
 *
 * @category Xodoa
 * @package Elastica
 * @author Fabian Vogler <fabian@equivalence.ch>
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/has-child-filter.html
 */
class HasChild extends AbstractFilter
{
    /**
     * Construct HasChild filter
     *
     * @param string|\Elastica\Query $query Query string or a Elastica\Query object
     * @param string                $type  Parent document type
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
     * @return \Elastica\Filter\HasChild                     Current object
     */
    public function setQuery($query)
    {
        $query = \Elastica\Query::create($query);
        $data = $query->toArray();

        return $this->setParam('query', $data['query']);
    }

    /**
     * Set type of the parent document
     *
     * @param  string                         $type Parent document type
     * @return \Elastica\Filter\HasChild Current object
     */
    public function setType($type)
    {
        return $this->setParam('type', $type);
    }

    /**
     * Sets the scope
     *
     * @param  string                         $scope Scope
     * @return \Elastica\Filter\HasChild Current object
     */
    public function setScope($scope)
    {
        return $this->setParam('_scope', $scope);
    }
}
