<?php

namespace Elastica\Aggregation;

/**
 * Class DateHistogram
 * @package Elastica\Aggregation
 * @link http://www.elasticsearch.org/guide/en/elasticsearch/reference/master/search-aggregations-bucket-datehistogram-aggregation.html
 */
class DateHistogram extends Histogram
{
    /**
     * Set pre-rounding based on interval
     * @param string $preZone
     * @return DateHistogram
     */
    public function setPreZone($preZone)
    {
        return $this->setParam("pre_zone", $preZone);
    }

    /**
     * Set post-rounding based on interval
     * @param string $postZone
     * @return DateHistogram
     */
    public function setPostZone($postZone)
    {
        return $this->setParam("post_zone", $postZone);
    }

    /**
     * Set pre-zone adjustment for larger time intervals (day and above)
     * @param string $adjust
     * @return DateHistogram
     */
    public function setPreZoneAdjustLargeInterval($adjust)
    {
        return $this->setParam("pre_zone_adjust_large_interval", $adjust);
    }

    /**
     * Adjust for granularity of date data
     * @param int $factor set to 1000 if date is stored in seconds rather than milliseconds
     * @return DateHistogram
     */
    public function setFactor($factor)
    {
        return $this->setParam("factor", $factor);
    }

    /**
     * Set the offset for pre-rounding
     * @param string $offset "1d", for example
     * @return DateHistogram
     */
    public function setPreOffset($offset)
    {
        return $this->setParam("pre_offset", $offset);
    }

    /**
     * Set the offset for post-rounding
     * @param string $offset "1d", for example
     * @return DateHistogram
     */
    public function setPostOffset($offset)
    {
        return $this->setParam("post_offset", $offset);
    }

    /**
     * Set the format for returned bucket key_as_string values
     * @link http://www.elasticsearch.org/guide/en/elasticsearch/reference/master/search-aggregations-bucket-daterange-aggregation.html#date-format-pattern
     * @param string $format see link for formatting options
     * @return DateHistogram
     */
    public function setFormat($format)
    {
        return $this->setParam("format", $format);
    }
} 