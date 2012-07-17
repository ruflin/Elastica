<?php
/**
 * Not Filter
 *
 * @uses Elastica_Filter_Abstract
 * @category Xodoa
 * @package Elastica
 * @author Lee Parker, Nicolas Ruflin <spam@ruflin.com>
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/not-filter.html
 */
class Elastica_Filter_Not extends Elastica_Filter_Abstract
{
    /**
     * Creates Not filter query
     *
     * @param Elastica_Filter_Abstract $filter Filter object
     */
    public function __construct(Elastica_Filter_Abstract $filter)
    {
        $this->setFilter($filter);
    }

    /**
     * Set filter
     *
     * @param  Elastica_Filter_Abstract $filter
     * @return Elastica_Filter_Not
     */
    public function setFilter(Elastica_Filter_Abstract $filter)
    {
        return $this->setParam('filter', $filter->toArray());
    }
}
