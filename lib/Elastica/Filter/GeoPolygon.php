<?php

/**
 * Geo polygon filter
 *
 * @uses Elastica_Query_Abstract
 * @category Xodoa
 * @package Elastica
 * @author Michael Maclean <mgdm@php.net>
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/geo-polygon-filter.html
 */
class Elastica_Filter_GeoPolygon extends Elastica_Filter_Abstract
{
    /**
     * Key
     *
     * @var string Key
     */
    protected $_key = '';

    /**
     * Points making up polygon
     *
     * @var array Points making up polygon
     */
    protected $_points = array();

    /**
     * Construct polygon filter
     *
     * @param string $key    Key
     * @param array  $points Points making up polygon
     */
    public function __construct($key, array $points)
    {
        $this->_key = $key;
        $this->_points = $points;
    }

    /**
     * Converts filter to array
     *
     * @see Elastica_Filter_Abstract::toArray()
     * @return array
     */
    public function toArray()
    {
        return array(
            'geo_polygon' => array(
                $this->_key => array(
                    'points' => $this->_points
                ),
            )
        );
    }
}
