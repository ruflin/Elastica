<?php
namespace Elastica\Query;

/**
 * Geo distance query.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-geo-distance-query.html
 */
class GeoDistance extends AbstractGeoDistance
{
    const DISTANCE_TYPE_ARC = 'arc';
    const DISTANCE_TYPE_PLANE = 'plane';

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
     * @param string $distanceType, default arc
     *
     * @return $this
     */
    public function setDistanceType($distanceType)
    {
        $this->setParam('distance_type', $distanceType);

        return $this;
    }
}
