<?php

namespace Elastica\Query;

use Elastica\Exception\InvalidException;

/**
 * Geo distance query.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-geo-distance-query.html
 */
abstract class AbstractGeoDistance extends AbstractQuery
{
    public const LOCATION_TYPE_GEOHASH = 'geohash';
    public const LOCATION_TYPE_LATLON = 'latlon';

    /**
     * Location type.
     *
     * Decides if this query uses latitude/longitude or geohash for the location.
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
     * @param array|string $location Location as array or geohash: array('lat' => 48.86, 'lon' => 2.35) OR 'drm3btev3e86'
     *
     * @internal param string $distance Distance
     */
    public function __construct(string $key, $location)
    {
        $this->setKey($key);
        $this->setLocation($location);
    }

    /**
     * @return $this
     */
    public function setKey(string $key): self
    {
        $this->_key = $key;

        return $this;
    }

    /**
     * @param array|string $location
     *
     * @throws InvalidException
     *
     * @return $this
     */
    public function setLocation($location): self
    {
        // Location
        if (\is_array($location)) { // Latitude/Longitude
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
        } elseif (\is_string($location)) { // Geohash
            $this->setGeohash($location);
        } else { // Invalid location
            throw new InvalidException('$location has to be an array (latitude/longitude) or a string (geohash)');
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function setLatitude(float $latitude): self
    {
        $this->_latitude = $latitude;
        $this->_locationType = self::LOCATION_TYPE_LATLON;

        return $this;
    }

    /**
     * @return $this
     */
    public function setLongitude(float $longitude): self
    {
        $this->_longitude = $longitude;
        $this->_locationType = self::LOCATION_TYPE_LATLON;

        return $this;
    }

    /**
     * @return $this
     */
    public function setGeohash(string $geohash): self
    {
        $this->_geohash = $geohash;
        $this->_locationType = self::LOCATION_TYPE_GEOHASH;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        $this->setParam($this->_key, $this->_getLocationData());

        return parent::toArray();
    }

    /**
     * @throws InvalidException
     *
     * @return array|string
     */
    protected function _getLocationData()
    {
        if (self::LOCATION_TYPE_LATLON === $this->_locationType) { // Latitude/longitude
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
        } elseif (self::LOCATION_TYPE_GEOHASH === $this->_locationType) { // Geohash
            $location = $this->_geohash;
        } else { // Invalid location type
            throw new InvalidException('Invalid location type');
        }

        return $location;
    }
}
