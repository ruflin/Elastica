<?php

namespace Elastica\Filter;

/**
 * Multi Abstract filter object. Should be extended by filter types composed of an array of sub filters
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
abstract class AbstractMulti extends AbstractFilter
{
    /**
     * Filters
     * @var array
     */
    protected $_filters = array();

    /**
     * Add filter
     *
     * @param  \Elastica\Filter\AbstractFilter $filter
     * @return \Elastica\Filter\AbstractMulti
     */
    public function addFilter(AbstractFilter $filter)
    {
        $this->_filters[] = $filter->toArray();

        return $this;
    }

    /**
     * Set filters
     *
     * @param  array                          $filters
     * @return \Elastica\Filter\AbstractMulti
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
     * @return array Filters
     */
    public function getFilters()
    {
        return $this->_filters;
    }

    /**
     * @see \Elastica\Param::toArray()
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
