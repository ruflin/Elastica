<?php

/**
 * Script filter
 *
 * @uses Elastica_Filter_Abstract
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/script-filter.html
 */
class Elastica_Filter_Script extends Elastica_Filter_Abstract
{
    /**
     * Query object
     *
     * @var array|Elastica_Query_Abstract
     */
    protected $_query = null;

    /**
     * Construct script filter
     *
     * @param array|Elastica_Query_Abstract $query OPTIONAL Query object
     */
    public function __construct($query = null)
    {
        if (!is_null($query)) {
            $this->setQuery($query);
        }
    }

    /**
     * Sets query object
     *
     * @param  array|Elastica_Query_Abstract $query
     * @return Elastica_Filter_Script
     * @throws Elastica_Exception_Invalid    Invalid argument type
     */
    public function setQuery($query)
    {
        // TODO: check if should be renamed to setScript?
        if (!$query instanceof Elastica_Query_Abstract && !is_array($query)) {
            throw new Elastica_Exception_Invalid('expected an array or instance of Elastica_Query_Abstract');
        }

        if ($query instanceof Elastica_Query_Abstract) {
            $this->_query = $query->toArray();
        } else {
            $this->_query = $query;
        }

        return $this;
    }

    /**
     * ToArray
     *
     * @return array Script filter
     * @see Elastica_Filter_Abstract::toArray()
     */
    public function toArray()
    {
        return array(
            'script' => (
                $this->_query
            ),
        );
    }
}
