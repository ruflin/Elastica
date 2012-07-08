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
	 * @param array|string $location Location as array or geohash: array('lat' => 48.86, 'lon' => 2.35) OR 'drm3btev3e86'
	 * @param string $distance Distance
	 */
	public function __construct($key, $location, $distance) {
		// Fix old constructor. Remove it when the old constructor is not supported anymore
		if(func_num_args() === 4) {
			$key = func_get_arg(0);
			$location = array(
				'lat' => func_get_arg(1),
				'lon' => func_get_arg(2)
			);
			$distance = func_get_arg(3);
		}
		
		$this->setParam($key, $location);
		$this->setParam('distance', $distance);
	}
	
	public function setDistanceType($distanceType) {
		$this->setParam('distance_type', $distanceType);
	}
	
	public function setOptimizeBbox($optimizeBbox) {
		$this->setParam('optimize_bbox', $optimizeBbox);
	}
}