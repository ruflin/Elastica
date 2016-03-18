<?php

namespace Elastica\Query;

/**
 * Geo polygon query.
 *
 * @author Michael Maclean <mgdm@php.net>
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-geo-polygon-query.html
 */
class GeoPolygon extends AbstractQuery
{
    /**
     * Key.
     *
     * @var string Key
     */
    protected $_key = '';

    /**
     * Points making up polygon.
     *
     * @var array Points making up polygon
     */
    protected $_points = array();

    /**
     * Construct polygon query.
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
     * Converts query to array.
     *
     * @see \Elastica\Query\AbstractQuery::toArray()
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            'geo_polygon' => array(
                $this->_key => array(
                    'points' => $this->_points,
                ),
            ),
        );
    }
}
