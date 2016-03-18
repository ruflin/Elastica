<?php

namespace Elastica\Query;

use Elastica\Exception\InvalidException;

/**
 * Geo distance query.
 *
 * @author munkie
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-geo-distance-range-query.html
 */
class GeoDistanceRange extends AbstractGeoDistance
{
    const RANGE_FROM = 'from';
    const RANGE_TO = 'to';
    const RANGE_LT = 'lt';
    const RANGE_LTE = 'lte';
    const RANGE_GT = 'gt';
    const RANGE_GTE = 'gte';

    const RANGE_INCLUDE_LOWER = 'include_lower';
    const RANGE_INCLUDE_UPPER = 'include_upper';

    /**
     * @var array
     */
    protected $_ranges = array();

    /**
     * @param string       $key
     * @param array|string $location
     * @param array        $ranges
     *
     * @internal param string $distance
     */
    public function __construct($key, $location, array $ranges = array())
    {
        parent::__construct($key, $location);

        if (!empty($ranges)) {
            $this->setRanges($ranges);
        }
    }

    /**
     * @param array $ranges
     *
     * @return $this
     */
    public function setRanges(array $ranges)
    {
        $this->_ranges = array();

        foreach ($ranges as $key => $value) {
            $this->setRange($key, $value);
        }

        return $this;
    }

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @throws \Elastica\Exception\InvalidException
     *
     * @return $this
     */
    public function setRange($key, $value)
    {
        switch ($key) {
            case self::RANGE_TO:
            case self::RANGE_FROM:
            case self::RANGE_GT:
            case self::RANGE_GTE:
            case self::RANGE_LT:
            case self::RANGE_LTE:
                break;
            case self::RANGE_INCLUDE_LOWER:
            case self::RANGE_INCLUDE_UPPER:
                $value = (boolean) $value;
                break;
            default:
                throw new InvalidException('Invalid range parameter given: '.$key);
        }
        $this->_ranges[$key] = $value;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        foreach ($this->_ranges as $key => $value) {
            $this->setParam($key, $value);
        }

        return parent::toArray();
    }
}
