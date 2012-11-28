<?php
/**
 * Geo distance filter
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/geo-distance-filter.html
 */
abstract class Elastica_Filter_Abstract_GeoDistance extends Elastica_Filter_Abstract
{

    const LOCATION_TYPE_GEOHASH = 'geohash';
    const LOCATION_TYPE_LATLON = 'latlon';

    /**
     * Location type
     *
     * Decides if this filter uses latitude/longitude or geohash for the location.
     * Values are "latlon" or "geohash".
     *
     * @var string
     */
    protected $_locationType = null;

    /**
     * Key
     *
     * @var string
     */
    protected $_key = null;

    /**
     * Latitude
     *
     * @var float
     */
    protected $_latitude = null;

    /**
     * Longitude
     *
     * @var float
     */
    protected $_longitude = null;

    /**
     * Geohash
     *
     * @var string
     */
    protected  $_geohash = null;

    /**
     * Create GeoDistance object
     *
     * @param  string                     $key      Key
     * @param  array|string               $location Location as array or geohash: array('lat' => 48.86, 'lon' => 2.35) OR 'drm3btev3e86'
     * @param  string                     $distance Distance
     * @throws Elastica_Exception_Invalid
     */
    public function __construct($key, $location)
    {
        // Key
        $this->setKey($key);
        $this->setLocation($location);
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
     * @param array|string $location
     * @return Elastica_Filter_GeoDistance
     * @throws Elastica_Exception_Invalid
     */
    public function setLocation($location)
    {
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

        return $this;
    }

    /**
     * @param  float                       $latitude
     * @return Elastica_Filter_GeoDistance current filter
     */
    public function setLatitude($latitude)
    {
        $this->_latitude = (float) $latitude;
        $this->_locationType = self::LOCATION_TYPE_LATLON;

        return $this;
    }

    /**
     * @param  float                       $longitude
     * @return Elastica_Filter_GeoDistance current filter
     */
    public function setLongitude($longitude)
    {
        $this->_longitude = (float) $longitude;
        $this->_locationType = self::LOCATION_TYPE_LATLON;

        return $this;
    }

    /**
     * @param  string                      $geohash
     * @return Elastica_Filter_GeoDistance current filter
     */
    public function setGeohash($geohash)
    {
        $this->_geohash = $geohash;
        $this->_locationType = self::LOCATION_TYPE_GEOHASH;

        return $this;
    }

    /**
     * @return array|string
     * @throws Elastica_Exception_Invalid
     */
    protected function _getLocationData()
    {
        if ($this->_locationType === self::LOCATION_TYPE_LATLON) { // Latitude/longitude
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
        } elseif ($this->_locationType === self::LOCATION_TYPE_GEOHASH) { // Geohash
            $location = $this->_geohash;
        } else { // Invalid location type
            throw new Elastica_Exception_Invalid('Invalid location type');
        }

        return $location;
    }

    /**
     * @see Elastica_Param::toArray()
     * @throws Elastica_Exception_Invalid
     */
    public function toArray()
    {
        $this->setParam($this->_key, $this->_getLocationData());

        return parent::toArray();
    }
}
