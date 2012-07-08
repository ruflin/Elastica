<?php
/**
 * Geo distance filter
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/geo-distance-filter.html
 */
class Elastica_Filter_GeoDistance extends Elastica_Filter_Abstract {
	/**
	 * Create GeoDistance object
	 *
	 * @param string $key Key
	 * @param float $latitude Latitude
	 * @param float $longitude Longitude
	 * @param string $distance Distance
	 */
	public function __construct($key, $latitude, $longitude, $distance) {
		$this->setParam($key, array(
			'lat' => (float)$latitude,
			'lon' => (float)$longitude
		));
		$this->setParam('distance', $distance);
	}
}