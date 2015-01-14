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
     * @param string|\Elastica\Query|\Elastica\Filter\AbstractFilter $query Query string or a Elastica\Query object or a filter
     * @param string|\Elastica\Type                                  $type  Child document type
     */
    public function __construct($query, $type = null)
    {
        $this->setType($type);
        if ($query instanceof AbstractFilter) {
            $this->setFilter($query);
        } else {
            $this->setQuery($query);
        }
    }

    /**
     * Sets query object
     *
     * @param  string|\Elastica\Query|\Elastica\Query\AbstractQuery $query
     * @return \Elastica\Filter\HasChild                            Current object
     */
    public function setQuery($query)
    {
        $query = \Elastica\Query::create($query);
        $data = $query->toArray();

        return $this->setParam('query', $data['query']);
    }

    /**
     * Sets the filter object
     *
     * @param  \Elastica\Filter\AbstractFilter $filter
     * @return \Elastica\Filter\HasChild       Current object
     */
    public function setFilter($filter)
    {
        return $this->setParam('filter', $filter->toArray());
    }

    /**
     * Set type of the child document
     *
     * @param  string|\Elastica\Type      $type Child document type
     * @return \Elastica\Filter\HasParent Current object
     */
    public function setType($type)
    {
        if ($type instanceof \Elastica\Type) {
            $type = $type->getName();
        }

        return $this->setParam('type', (string) $type);
    }

    /**
     * Set minimum number of children are required to match for the parent doc to be considered a match
     * @param  int                       $count
     * @return \Elastica\Filter\HasChild
     */
    public function setMinimumChildrenCount($count)
    {
        return $this->setParam('min_children', (int) $count);
    }

    /**
     * Set maximum number of children are required to match for the parent doc to be considered a match
     * @param  int                       $count
     * @return \Elastica\Filter\HasChild
     */
    public function setMaximumChildrenCount($count)
    {
        return $this->setParam('max_children', (int) $count);
    }
}
