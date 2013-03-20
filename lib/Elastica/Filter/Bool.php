<?php

namespace Elastica\Filter;

use Elastica\Exception\InvalidException;

/**
 * Bool Filter
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/bool-query.html
 */
class Bool extends AbstractFilter
{
    /**
     * @var float
     */
    protected $_boost = 1.0;

    /**
     * Must
     *
     * @var array
     */
    protected $_must = array();

    /**
     * Should
     *
     * @var array
     */
    protected $_should = array();

    /**
     * Must not
     *
     * @var array
     */
    protected $_mustNot = array();

    /**
     * Adds should filter
     *
     * @param  array|\Elastica\Filter\AbstractFilter $args Filter data
     * @return \Elastica\Filter\Bool           Current object
     */
    public function addShould($args)
    {
        return $this->_addFilter('should', $args);
    }

    /**
     * Adds must filter
     *
     * @param  array|\Elastica\Filter\AbstractFilter $args Filter data
     * @return \Elastica\Filter\Bool           Current object
     */
    public function addMust($args)
    {
        return $this->_addFilter('must', $args);
    }

    /**
     * Adds mustNot filter
     *
     * @param  array|\Elastica\Filter\AbstractFilter $args Filter data
     * @return \Elastica\Filter\Bool           Current object
     */
    public function addMustNot($args)
    {
        return $this->_addFilter('mustNot', $args);
    }

    /**
     * Adds general filter based on type
     *
     * @param  string                               $type Filter type
     * @param  array|\Elastica\Filter\AbstractFilter $args Filter data
     * @throws \Elastica\Exception\InvalidException
     * @return \Elastica\Filter\Bool           Current object
     */
    protected function _addFilter($type, $args)
    {
        if ($args instanceof AbstractFilter) {
            $args = $args->toArray();
        }

        if (!is_array($args)) {
            throw new InvalidException('Invalid parameter. Has to be array or instance of Elastica\Filter');
        }

        $varName = '_' . $type;
        $this->{$varName}[] = $args;

        return $this;
    }

    /**
     * Converts bool filter to array
     *
     * @see \Elastica\Filter\AbstractFilter::toArray()
     * @return array Filter array
     */
    public function toArray()
    {
        $args = array();

        if (!empty($this->_must)) {
            $args['bool']['must'] = $this->_must;
        }

        if (!empty($this->_should)) {
            $args['bool']['should'] = $this->_should;
        }

        if (!empty($this->_mustNot)) {
            $args['bool']['must_not'] = $this->_mustNot;
        }

        return $args;
    }

    /**
     * Sets the boost value for this filter
     *
     * @param  float                      $boost Boost
     * @return \Elastica\Filter\Bool Current object
     */
    public function setBoost($boost)
    {
        $this->_boost = $boost;

        return $this;
    }

}
