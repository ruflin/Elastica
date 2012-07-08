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
	const DISTANCE_TYPE_ARC = 'arc';
	const DISTANCE_TYPE_PLANE = 'plane';
	
	const OPTIMIZE_BBOX_MEMORY = 'memory';
	const OPTIMIZE_BBOX_INDEXED = 'indexed';
	const OPTIMIZE_BBOX_NONE = 'none';
	
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
	
	public function setDistanceType($distanceType) {
		$this->setParam('distance_type', $distanceType);
	}
	
	public function setOptimizeBbox($optimizeBbox) {
		$this->setParam('optimize_bbox', $optimizeBbox);
	}
}