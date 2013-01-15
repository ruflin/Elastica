<?php

namespace Elastica\Filter;

/**
 * Not Filter
 *
 * @category Xodoa
 * @package Elastica
 * @author Lee Parker, Nicolas Ruflin <spam@ruflin.com>
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/not-filter.html
 */
class Not extends AbstractFilter
{
    /**
     * Creates Not filter query
     *
     * @param \Elastica\Filter\AbstractFilter $filter Filter object
     */
    public function __construct(AbstractFilter $filter)
    {
        $this->setFilter($filter);
    }

    /**
     * Set filter
     *
     * @param  \Elastica\Filter\AbstractFilter $filter
     * @return \Elastica\Filter\Not
     */
    public function setFilter(AbstractFilter $filter)
    {
        return $this->setParam('filter', $filter->toArray());
    }
}
