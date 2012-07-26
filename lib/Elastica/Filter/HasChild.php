<?php

/**
 * Returns parent documents having child docs matching the query
 *
 * @uses Elastica_Filter_Abstract
 * @category Xodoa
 * @package Elastica
 * @author Fabian Vogler <fabian@equivalence.ch>
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/has-child-filter.html
 */
class Elastica_Filter_HasChild extends Elastica_Filter_Abstract
{
    /**
     * Construct HasChild filter
     *
     * @param string|Elastica_Query $query Query string or a Elastica_Query object
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
     * @param  string|Elastica_Query|Elastica_Query_Abstract $query
     * @return Elastica_Filter_HasChild                      Current object
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
     * @param  string                   $type Parent document type
     * @return Elastica_Filter_HasChild Current object
     */
    public function setType($type)
    {
        return $this->setParam('type', $type);
    }
}
