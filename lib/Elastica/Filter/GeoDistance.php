<?php
/**
 * Geo distance filter
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/geo-distance-filter.html
 */
class Elastica_Filter_GeoDistance extends Elastica_Filter_Abstract_GeoDistance
{
    const DISTANCE_TYPE_ARC = 'arc';
    const DISTANCE_TYPE_PLANE = 'plane';

    const OPTIMIZE_BBOX_MEMORY = 'memory';
    const OPTIMIZE_BBOX_INDEXED = 'indexed';
    const OPTIMIZE_BBOX_NONE = 'none';

    /**
     * Create GeoDistance object
     *
     * @param  string                     $key      Key
     * @param  array|string               $location Location as array or geohash: array('lat' => 48.86, 'lon' => 2.35) OR 'drm3btev3e86'
     * @param  string                     $distance Distance
     * @throws Elastica_Exception_Invalid
     */
    public function __construct($key, $location, $distance)
    {
        // Fix old constructor. Remove it when the old constructor is not supported anymore
        if (func_num_args() === 4) {
            extract($this->_oldConstruct(func_get_args()));
        }

        parent::__construct($key, $location);

        $this->setDistance($distance);
    }

    /**
     * Convert old constructor signature to the new one
     * Remove it when the old constructor is not supported
     *
     * @deprecated
     * @param  array $args old arguments
     * @return array new arguments
     */
    private function _oldConstruct(array $args)
    {
        return array(
            'key' => $args[0],
            'location' => array(
                'lat' => $args[1],
                'lon' => $args[2]
            ),
            'distance' => $args[3]
        );
    }

    /**
     * @param  string                      $distance
     * @return Elastica_Filter_GeoDistance current filter
     */
    public function setDistance($distance)
    {
        $this->setParam('distance', $distance);

        return $this;
    }

    /**
     * See DISTANCE_TYPE_* constants
     *
     * @param  string                      $distanceType
     * @return Elastica_Filter_GeoDistance current filter
     */
    public function setDistanceType($distanceType)
    {
        $this->setParam('distance_type', $distanceType);

        return $this;
    }

    /**
     * See OPTIMIZE_BBOX_* constants
     *
     * @param  string                      $optimizeBbox
     * @return Elastica_Filter_GeoDistance current filter
     */
    public function setOptimizeBbox($optimizeBbox)
    {
        $this->setParam('optimize_bbox', $optimizeBbox);

        return $this;
    }
}
