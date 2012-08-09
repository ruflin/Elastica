<?php
/**
 * Bool Filter
 *
 * @uses Elastica_Filter_Abstract
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/bool-query.html
 */
class Elastica_Filter_Bool extends Elastica_Filter_Abstract
{
    /**
     * minimum number of matches
     *
     * @var int minimum number of matches
     */
    protected $_minimumNumberShouldMatch = 1;

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
     * @param  array|Elastica_Filter_Abstract $args Filter data
     * @return Elastica_Filter_Bool           Current object
     */
    public function addShould($args)
    {
        return $this->_addFilter('should', $args);
    }

    /**
     * Adds must filter
     *
     * @param  array|Elastica_Filter_Abstract $args Filter data
     * @return Elastica_Filter_Bool           Current object
     */
    public function addMust($args)
    {
        return $this->_addFilter('must', $args);
    }

    /**
     * Adds mustNot filter
     *
     * @param  array|Elastica_Filter_Abstract $args Filter data
     * @return Elastica_Filter_Bool           Current object
     */
    public function addMustNot($args)
    {
        return $this->_addFilter('mustNot', $args);
    }

    /**
     * Adds general filter based on type
     *
     * @param  string                         $type Filter type
     * @param  array|Elastica_Filter_Abstract $args Filter data
     * @return Elastica_Filter_Bool           Current object
     */
    protected function _addFilter($type, $args)
    {
        if ($args instanceof Elastica_Filter_Abstract) {
            $args = $args->toArray();
        }

        if (!is_array($args)) {
            throw new Elastica_Exception_Invalid('Invalid parameter. Has to be array or instance of Elastica_Filter');
        }

        $varName = '_' . $type;
        $this->{$varName}[] = $args;

        return $this;
    }

    /**
     * Converts bool filter to array
     *
     * @see Elastica_Filter_Abstract::toArray()
     * @return array Filter array
     */
    public function toArray()
    {
        $args = array();

        if (!empty($this->_must)) {
            $args['must'] = $this->_must;
        }

        if (!empty($this->_should)) {
            $args['should'] = $this->_should;
        }

        if (!empty($this->_mustNot)) {
            $args['must_not'] = $this->_mustNot;
        }

        return array('bool' => $args);
    }

    /**
     * Sets the boost value for this filter
     *
     * @param  float                $boost Boost
     * @return Elastica_Filter_Bool Current object
     */
    public function setBoost($boost)
    {
        $this->_boost = $boost;

        return $this;
    }

    /**
     * Sets the minimum number that should filter have to match
     *
     * @param  int                  $minimumNumberShouldMatch Number of matches
     * @return Elastica_Filter_Bool Current object
     */
    public function setMinimumNumberShouldMatch($minimumNumberShouldMatch)
    {
        $this->_minimumNumberShouldMatch = intval($minimumNumberShouldMatch);

        return $this;
    }
}
