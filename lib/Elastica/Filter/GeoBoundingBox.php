<?php

/**
 * Geo bounding box filter
 *
 * @uses Elastica_Query_Abstract
 * @category Xodoa
 * @package Elastica
 * @author Fabian Vogler <fabian@equivalence.ch>
 * @link http://www.elasticsearch.com/docs/elasticsearch/rest_api/query_dsl/geo_bounding_box_filter/
 */
class Elastica_Filter_GeoBoundingBox extends Elastica_Filter_Abstract
{
	protected $_key;
	protected $_topLeftLatitude;
	protected $_topLeftLongitude;
	protected $_bottomRightLatitude;
	protected $_bottomRightLongitude;

	/**
	 * @param string $key Key
	 * @param string $topLeftLatitude
	 * @param string $topLeftLongitude
	 * @param string $bottomRightLatitude
	 * @param string $bottomRightLongitude
	 */
	public function __construct($key, $topLeftLatitude, $topLeftLongitude, $bottomRightLatitude, $bottomRightLongitude) {
		$this->_key = $key;
		$this->_topLeftLatitude = $topLeftLatitude;
		$this->_topLeftLongitude = $topLeftLongitude;
		$this->_bottomRightLatitude = $bottomRightLatitude;
		$this->_bottomRightLongitude = $bottomRightLongitude;
	}

	/**
	 * Converts filter to array
	 *
	 * @see Elastica_Filter_Abstract::toArray()
	 * @return Elastica_Filter_GeoDistance Current object
	 */
	public function toArray() {
		return array(
			'geo_bounding_box' => array(
				$this->_key => array(
					'top_left' => array(
						'lat' => $this->_topLeftLatitude,
						'lon' => $this->_topLeftLongitude
					),
					'bottom_right' => array(
						'lat' => $this->_bottomRightLatitude,
						'lon' => $this->_bottomRightLongitude
					)
				),
			)
		);
	}
}
