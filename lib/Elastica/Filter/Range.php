<?php

namespace Elastica\Filter;

/**
 * Range Filter
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/range-filter.html
 */
class Range extends AbstractFilter
{
    /**
     * Fields
     *
     * @var array Fields
     */
    protected $_fields = array();

    /**
     * Construct range filter
     *
     * @param string $fieldName Field name
     * @param array  $args      Field arguments
     */
    public function __construct($fieldName = '', array $args = array())
    {
        if ($fieldName) {
            $this->addField($fieldName, $args);
        }
    }

    /**
     * Ads a field with arguments to the range query
     *
     * @param  string                 $fieldName Field name
     * @param  array                  $args      Field arguments
     * @return \Elastica\Filter\Range
     */
    public function addField($fieldName, array $args)
    {
        $this->_fields[$fieldName] = $args;

        return $this;
    }

    /**
     * Set execution mode
     *
     * @param  string                 $execution Options: "index" or "fielddata"
     * @return \Elastica\Filter\Range
     */
    public function setExecution($execution)
    {
        return $this->setParam('execution', (string) $execution);
    }

    /**
     * Converts object to array
     *
     * @see \Elastica\Filter\AbstractFilter::toArray()
     * @return array Filter array
     */
    public function toArray()
    {
        $this->setParams(array_merge($this->getParams(), $this->_fields));
        return parent::toArray();
    }
}
