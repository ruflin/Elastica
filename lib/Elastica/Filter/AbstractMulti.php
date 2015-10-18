<?php
namespace Elastica\Filter;

/**
 * Multi Abstract filter object. Should be extended by filter types composed of an array of sub filters.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
abstract class AbstractMulti extends AbstractFilter
{
    /**
     * Filters.
     *
     * @var array
     */
    protected $_filters = array();

    /**
     * @param array $filters
     */
    public function __construct(array $filters = array())
    {
        if (!empty($filters)) {
            $this->setFilters($filters);
        }
    }

    /**
     * Add filter.
     *
     * @param \Elastica\Filter\AbstractFilter $filter
     *
     * @return $this
     */
    public function addFilter(AbstractFilter $filter)
    {
        $this->_filters[] = $filter;

        return $this;
    }

    /**
     * Set filters.
     *
     * @param array $filters
     *
     * @return $this
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
     *
     * @return array
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

        return $this->_convertArrayable($data);
    }
}
