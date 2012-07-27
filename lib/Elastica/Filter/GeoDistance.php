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
    const DISTANCE_TYPE_ARC = 'arc';
    const DISTANCE_TYPE_PLANE = 'plane';

    const OPTIMIZE_BBOX_MEMORY = 'memory';
    const OPTIMIZE_BBOX_INDEXED = 'indexed';
    const OPTIMIZE_BBOX_NONE = 'none';

    /**
     * Location type
     *
     * Decides if this filter uses latitude/longitude or geohash for the location.
     * Values are "latlon" or "geohash".
     *
     * @var string
     */
    private $_locationType = null;

    /**
     * Key
     *
     * @var string
     */
    private $_key = null;

    /**
     * Latitude
     *
     * @var float
     */
    private $_latitude = null;

    /**
     * Longitude
     *
     * @var float
     */
    private $_longitude = null;

    /**
     * Geohash
     *
     * @var string
     */
    private $_geohash = null;

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

        // Key
        $this->setKey($key);

        // Location
        if (is_array($location)) { // Latitude/Longitude
            // Latitude
            if (isset($location['lat'])) {
                $this->setLatitude($location['lat']);
            } else {
                throw new Elastica_Exception_Invalid('$location[\'lat\'] has to be set');
            }

            // Longitude
            if (isset($location['lon'])) {
                $this->setLongitude($location['lon']);
            } else {
                throw new Elastica_Exception_Invalid('$location[\'lon\'] has to be set');
            }
        } elseif (is_string($location)) { // Geohash
            $this->setGeohash($location);
        } else { // Invalid location
            throw new Elastica_Exception_Invalid('$location has to be an array (latitude/longitude) or a string (geohash)');
        }

        //Distance
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
     * @param  string                      $key
     * @return Elastica_Filter_GeoDistance current filter
     */
    public function setKey($key)
    {
        $this->_key = $key;

        return $this;
    }

    /**
     * @param  float                       $latitude
     * @return Elastica_Filter_GeoDistance current filter
     */
    public function setLatitude($latitude)
    {
        $this->_latitude = (float) $latitude;
        $this->_locationType = 'latlon';

        return $this;
    }

    /**
     * @param  float                       $longitude
     * @return Elastica_Filter_GeoDistance current filter
     */
    public function setLongitude($longitude)
    {
        $this->_longitude = (float) $longitude;
        $this->_locationType = 'latlon';

        return $this;
    }

    /**
     * @param  string                      $geohash
     * @return Elastica_Filter_GeoDistance current filter
     */
    public function setGeohash($geohash)
    {
        $this->_geohash = $geohash;
        $this->_locationType = 'geohash';

        return $this;
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

    /**
     * @see Elastica_Param::toArray()
     * @throws Elastica_Exception_Invalid
     */
    public function toArray()
    {
        $data = parent::toArray();

        // Add location to data array
        $filterName = $this->_getBaseName();
        $filterData = $data[$filterName];

        if ($this->_locationType === 'latlon') { // Latitude/longitude
            $location = array();

            if (isset($this->_latitude)) { // Latitude
                $location['lat'] = $this->_latitude;
            } else {
                throw new Elastica_Exception_Invalid('Latitude has to be set');
            }

            if (isset($this->_longitude)) { // Geohash
                $location['lon'] = $this->_longitude;
            } else {
                throw new Elastica_Exception_Invalid('Longitude has to be set');
            }
        } elseif ($this->_locationType === 'geohash') { // Geohash
            $location = $this->_geohash;
        } else { // Invalid location type
            throw new Elastica_Exception_Invalid('Invalid location type');
        }

        $filterData[$this->_key] = $location;

        $data[$filterName] = $filterData;

        return $data;
    }
}
