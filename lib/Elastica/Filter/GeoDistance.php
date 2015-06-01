<?php
namespace Elastica\Filter;

/**
 * Geo distance filter.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 *
 * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-geo-distance-filter.html
 */
class GeoDistance extends AbstractGeoDistance
{
    const DISTANCE_TYPE_ARC = 'arc';
    const DISTANCE_TYPE_PLANE = 'plane';
    const DISTANCE_TYPE_SLOPPY_ARC = 'sloppy_arc';

    const OPTIMIZE_BBOX_MEMORY = 'memory';
    const OPTIMIZE_BBOX_INDEXED = 'indexed';
    const OPTIMIZE_BBOX_NONE = 'none';

    /**
     * Create GeoDistance object.
     *
     * @param string       $key      Key
     * @param array|string $location Location as array or geohash: array('lat' => 48.86, 'lon' => 2.35) OR 'drm3btev3e86'
     * @param string       $distance Distance
     *
     * @throws \Elastica\Exception\InvalidException
     */
    public function __construct($key, $location, $distance)
    {
        parent::__construct($key, $location);

        $this->setDistance($distance);
    }

    /**
     * @param string $distance
     *
     * @return $this
     */
    public function setDistance($distance)
    {
        $this->setParam('distance', $distance);

        return $this;
    }

    /**
     * See DISTANCE_TYPE_* constants.
     *
     * @param string $distanceType
     *
     * @return $this
     */
    public function setDistanceType($distanceType)
    {
        $this->setParam('distance_type', $distanceType);

        return $this;
    }

    /**
     * See OPTIMIZE_BBOX_* constants.
     *
     * @param string $optimizeBbox
     *
     * @return $this
     */
    public function setOptimizeBbox($optimizeBbox)
    {
        $this->setParam('optimize_bbox', $optimizeBbox);

        return $this;
    }
}
