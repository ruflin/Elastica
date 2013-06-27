<?php

namespace Elastica\Filter;

use Elastica\Filter\AbstractFilter;

/**
 * geo_shape filter or provided shapes
 *
 * Filter provided shape definitions
 *
 * @category Xodoa
 * @package Elastica
 * @author Christian Hansen <quid@gmx.de>
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/geo-shape-filter/
 */
class GeoShape extends AbstractFilter
{
    /**
     * Key
     *
     * @var string Key
     */
    protected $_key = '';

	/**
     * Type of the geo_shape
     *
     * @var string Type
     */
    protected $_type = '';

    /**
     * Coordinates making up geo_shape
     *
     * @var array Coordinates making up geo_shape
     */
    protected $_coordinates = array();

    /**
     * Construct geo_shape filter
     *
     * @param string $key			Key
     * @param string $type			Type of the geo_shape:
	 *								point, envelope, linestring, polygon,
	 *								multipoint or multipolygon
     * @param array $coordinates	Points making up the shape
     */
    public function __construct($key, $type, array $coordinates = array())
    {
        $this->_key = $key;
        $this->_type = $type;
        $this->_coordinates = $coordinates;
    }

    /**
     * Converts filter to array
     *
     * @see \Elastica\Filter\AbstractFilter::toArray()
     * @return array
     */
    public function toArray()
    {
        return array(
            'geo_shape' => array(
                $this->_key => array(
					'shape' => array(
						'type' => $this->_type,
						'coordinates' => $this->_coordinates
					)
                )
            )
        );
    }
}
