<?php
/**
 * Geo distance filter
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/geo-distance-filter.html
 */
class Elastica_Filter_GeoDistance extends Elastica_Filter_Abstract
{
	protected $_key;
	protected $_distance;
	protected $_latitude;
	protected $_longitude;

	/**
	 * Create GeoDistance object
	 *
	 * @param string $key Key
	 * @param string $latitude Latitude
	 * @param string $longitude Longitude
	 * @param string $distance Distance
	 */
	public function __construct($key, $latitude, $longitude, $distance) {
		$this->_key = $key;
		$this->setLatitude($latitude);
		$this->setLongitude($longitude);
		$this->setDistance($distance);
	}

	/**
	 * Sets the distance to search for
	 *
	 * @param string $distance Distance
	 * @return Elastica_Filter_GeoDistance Current object
	 */
	public function setDistance($distance) {
		// TODO: validate distance?
		$this->_distance = $distance;
		return $this;
	}

	/**
	 * Sets the laititude
	 *
	 * @param string $latitude Latitude
	 * @return Elastica_Filter_GeoDistance Current object
	 */
	public function setLatitude($latitude) {
		$this->_latitude = $latitude;
		return $this;
	}


	/**
	 * Sets the longitude
	 *
	 * @param string $longitude Longitude
	 * @return Elastica_Filter_GeoDistance Current object
	 */
	public function setLongitude($longitude) {
		$this->_longitude = $longitude;
		return $this;
	}

	/**
	 * Convers filter o array
	 *
	 * @see Elastica_Filter_Abstract::toArray()
	 * @return Elastica_Filter_GeoDistance Current object
	 */
	public function toArray() {
		return array(
			'geo_distance' => array(
				'distance' => $this->_distance,
				$this->_key => array(
					'lat' => $this->_latitude,
					'lon' => $this->_longitude
				),
			),
		);
	}
}