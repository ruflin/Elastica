<?php
/**
 * Geo distance filter
 *
 * @link http://www.elasticsearch.com/docs/elasticsearch/rest_api/query_dsl/geo_distance_filter/
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class Elastica_Filter_GeoDistance extends Elastica_Filter_Abstract
{
	protected $_key;
	protected $_distance;
	protected $_latitude;
	protected $_longitude;
	
	public function __construct($key, $latitude, $longitude, $distance) {
		$this->_key = $key;
		$this->setLatitude($latitude);
		$this->setLongitude($longitude);
		$this->setDistance($distance);
	}
		
	public function setDistance($distance) {
		// TODO: validate distance?
		$this->_distance = $distance;
	}
	
	public function setLatitude($latitude) {
		$this->_latitude = $latitude;
	}
	
	public function setLongitude($longitude) {
		$this->_longitude = $longitude;
	}
	
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