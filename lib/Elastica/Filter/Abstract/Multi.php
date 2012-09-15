<?php

/**
 * Multi Abstract filter object. Should be extended by filter types composed of an array of sub filters
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
abstract class Elastica_Filter_Abstract_Multi extends Elastica_Filter_Abstract
{
    /**
     * Filters
     * @var array
     */
    protected $_filters = array();

    /**
     * Add filter
     *
     * @param  Elastica_Filter_Abstract       $filter
     * @return Elastica_Filter_Abstract_Multi
     */
    public function addFilter(Elastica_Filter_Abstract $filter)
    {
        $this->_filters[] = $filter->toArray();

        return $this;
    }

    /**
     * Set filters
     *
     * @param  array                          $filters
     * @return Elastica_Filter_Abstract_Multi
     */
    public function setFilters(array $filters)
    {
        $this->_filters = array();

        foreach ($filters as $filter) {
            $this->addFilter($filter);
        }

        return $this;
    }

    /**
     * @see Elastica_Param::toArray()
     */
    public function toArray()
    {
        $data = parent::toArray();
        $name = $this->_getBaseName();
        $filterData = $data[$name];

        if (empty($filterData)) {
            $filterData = $this->_filters;
        } else {
            $filterData['filters'] = $this->_filters;
        }

        $data[$name] = $filterData;

        return $data;
    }
}
