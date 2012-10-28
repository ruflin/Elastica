<?php

/**
 * Returns child documents having parent docs matching the query
 *
 * @uses Elastica_Filter_Abstract
 * @category Xodoa
 * @package Elastica
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/has-parent-filter.html
 */
class Elastica_Filter_HasParent extends Elastica_Filter_Abstract
{
    /**
     * Construct HasParent filter
     *
     * @param string|Elastica_Query $query Query string or a Elastica_Query object
     * @param string                $type  Parent document type
     */
    public function __construct($query, $type)
    {
        $this->setQuery($query);
        $this->setType($type);
    }

    /**
     * Sets query object
     *
     * @param  string|Elastica_Query|Elastica_Query_Abstract $query
     * @return Elastica_Filter_HasParent                     Current object
     */
    public function setQuery($query)
    {
        $query = Elastica_Query::create($query);
        $data = $query->toArray();

        return $this->setParam('query', $data['query']);
    }

    /**
     * Set type of the parent document
     *
     * @param  string                    $type Parent document type
     * @return Elastica_Filter_HasParent Current object
     */
    public function setType($type)
    {
        return $this->setParam('type', $type);
    }

    /**
     * Sets the scope
     *
     * @param  string                    $scope Scope
     * @return Elastica_Filter_HasParent Current object
     */
    public function setScope($scope)
    {
        return $this->setParam('_scope', $scope);
    }
}
