<?php
namespace Elastica\Filter;

use Elastica\Exception\InvalidException;

trigger_error('Deprecated: Filters are deprecated. Use queries in filter context. See https://www.elastic.co/guide/en/elasticsearch/reference/2.0/query-dsl-filters.html', E_USER_DEPRECATED);

/**
 * Geo distance filter.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-geo-distance-filter.html
 * @deprecated Filters are deprecated. Use queries in filter context. See https://www.elastic.co/guide/en/elasticsearch/reference/2.0/query-dsl-filters.html
 */
abstract class AbstractGeoDistance extends AbstractFilter
{
    const LOCATION_TYPE_GEOHASH = 'geohash';
    const LOCATION_TYPE_LATLON = 'latlon';

    /**
     * Location type.
     *
     * Decides if this filter uses latitude/longitude or geohash for the location.
     * Values are "latlon" or "geohash".
     *
     * @var string
     */
    protected $_locationType;

    /**
     * Key.
     *
     * @var string
     */
    protected $_key;

    /**
     * Latitude.
     *
     * @var float
     */
    protected $_latitude;

    /**
     * Longitude.
     *
     * @var float
     */
    protected $_longitude;

    /**
     * Geohash.
     *
     * @var string
     */
    protected $_geohash;

    /**
     * Create GeoDistance object.
     *
     * @param string       $key      Key
     * @param array|string $location Location as array or geohash: array('lat' => 48.86, 'lon' => 2.35) OR 'drm3btev3e86'
     *
     * @internal param string $distance Distance
     */
    public function __construct($key, $location)
    {
        $this->setKey($key);
        $this->setLocation($location);
    }

    /**
     * @param string $key
     *
     * @return $this
     */
    public function setKey($key)
    {
        $this->_key = $key;

        return $this;
    }

    /**
     * @param array|string $location
     *
     * @throws \Elastica\Exception\InvalidException
     *
     * @return $this
     */
    public function setLocation($location)
    {
        // Location
        if (is_array($location)) { // Latitude/Longitude
            // Latitude
            if (isset($location['lat'])) {
                $this->setLatitude($location['lat']);
            } else {
                throw new InvalidException('$location[\'lat\'] has to be set');
            }

            // Longitude
            if (isset($location['lon'])) {
                $this->setLongitude($location['lon']);
            } else {
                throw new InvalidException('$location[\'lon\'] has to be set');
            }
        } elseif (is_string($location)) { // Geohash
            $this->setGeohash($location);
        } else { // Invalid location
            throw new InvalidException('$location has to be an array (latitude/longitude) or a string (geohash)');
        }

        return $this;
    }

    /**
     * @param float $latitude
     *
     * @return $this
     */
    public function setLatitude($latitude)
    {
        $this->_latitude = (float) $latitude;
        $this->_locationType = self::LOCATION_TYPE_LATLON;

        return $this;
    }

    /**
     * @param float $longitude
     *
     * @return $this
     */
    public function setLongitude($longitude)
    {
        $this->_longitude = (float) $longitude;
        $this->_locationType = self::LOCATION_TYPE_LATLON;

        return $this;
    }

    /**
     * @param string $geohash
     *
     * @return $this
     */
    public function setGeohash($geohash)
    {
        $this->_geohash = $geohash;
        $this->_locationType = self::LOCATION_TYPE_GEOHASH;

        return $this;
    }

    /**
     * @throws \Elastica\Exception\InvalidException
     *
     * @return array|string
     */
    protected function _getLocationData()
    {
        if ($this->_locationType === self::LOCATION_TYPE_LATLON) { // Latitude/longitude
            $location = [];

            if (isset($this->_latitude)) { // Latitude
                $location['lat'] = $this->_latitude;
            } else {
                throw new InvalidException('Latitude has to be set');
            }

            if (isset($this->_longitude)) { // Geohash
                $location['lon'] = $this->_longitude;
            } else {
                throw new InvalidException('Longitude has to be set');
            }
        } elseif ($this->_locationType === self::LOCATION_TYPE_GEOHASH) { // Geohash
            $location = $this->_geohash;
        } else { // Invalid location type
            throw new InvalidException('Invalid location type');
        }

        return $location;
    }

    /**
     * @see \Elastica\Param::toArray()
     *
     * @throws \Elastica\Exception\InvalidException
     *
     * @return array
     */
    public function toArray()
    {
        $this->setParam($this->_key, $this->_getLocationData());

        return parent::toArray();
    }
}
