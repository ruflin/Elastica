<?php

/**
 * Geo polygon filter
 *
 * @uses Elastica_Query_Abstract
 * @category Xodoa
 * @package Elastica
 * @author Michael Maclean <mgdm@php.net>
 * @link http://www.elasticsearch.com/docs/elasticsearch/rest_api/query_dsl/geo_bounding_box_filter/
 */
class Elastica_Filter_GeoPolygon extends Elastica_Filter_Abstract
{
	protected $_key;
	protected $_points;

	/**
	 * @param string $key Key
	 * @param array $points Points making up polygon
	 */
	public function __construct($key, array $points) {
		$this->_key = $key;
		$this->_points = $points;
	}

	/**
	 * Converts filter to array
	 *
	 * @see Elastica_Filter_Abstract::toArray()
	 * @return array
	 */
	public function toArray() {
		return array(
			'geo_polygon' => array(
				$this->_key => array(
					'points' => $this->_points
				),
			)
		);
	}
}
