<?php
/**
 * Geo distance filter
 *
 * @category Xodoa
 * @package Elastica
 * @author munkie
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/geo-distance-range-filter.html
 */
class Elastica_Filter_GeoDistanceRange extends Elastica_Filter_GeoDistance_Abstract
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
     * @param string $key
     * @param array|string $location
     * @param string $distance
     * @param array $ranges
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
     * @return Elastica_Filter_GeoDistanceRange
     */
    public function setRanges(array $ranges)
    {
        foreach ($ranges as $key => $value) {
            $this->setRange($key, $value);
        }

        return $this;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return Elastica_Filter_GeoDistanceRange
     * @throws Elastica_Exception_Invalid
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
                throw new Elastica_Exception_Invalid('Invalid range parameter given: ' . $key);
        }
        $this->setParam($key, $value);

        return $this;
    }
}