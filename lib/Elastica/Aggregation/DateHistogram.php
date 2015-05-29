<?php
namespace Elastica\Aggregation;

/**
 * Class DateHistogram.
 *
 * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-datehistogram-aggregation.html
 */
class DateHistogram extends Histogram
{
    /**
     * Set pre-rounding based on interval.
     *
     * @deprecated Option "pre_zone" is deprecated as of ES 1.5. Use "time_zone" instead
     *
     * @param string $preZone
     *
     * @return $this
     */
    public function setPreZone($preZone)
    {
        return $this->setParam('pre_zone', $preZone);
    }

    /**
     * Set post-rounding based on interval.
     *
     * @deprecated Option "post_zone" is deprecated as of ES 1.5. Use "time_zone" instead
     *
     * @param string $postZone
     *
     * @return $this
     */
    public function setPostZone($postZone)
    {
        return $this->setParam('post_zone', $postZone);
    }

    /**
     * Set time_zone option.
     *
     * @param  string
     *
     * @return $this
     */
    public function setTimezone($timezone)
    {
        return $this->setParam('time_zone', $timezone);
    }

    /**
     * Set pre-zone adjustment for larger time intervals (day and above).
     *
     * @deprecated Option "pre_zone_adjust_large_interval" is deprecated as of ES 1.5
     *
     * @param string $adjust
     *
     * @return $this
     */
    public function setPreZoneAdjustLargeInterval($adjust)
    {
        return $this->setParam('pre_zone_adjust_large_interval', $adjust);
    }

    /**
     * Adjust for granularity of date data.
     *
     * @param int $factor set to 1000 if date is stored in seconds rather than milliseconds
     *
     * @return $this
     */
    public function setFactor($factor)
    {
        return $this->setParam('factor', $factor);
    }

    /**
     * Set the offset for pre-rounding.
     *
     * @deprecated Option "pre_offset" is deprecated as of ES 1.5. Use "offset" instead
     *
     * @param string $offset "1d", for example
     *
     * @return $this
     */
    public function setPreOffset($offset)
    {
        return $this->setParam('pre_offset', $offset);
    }

    /**
     * Set the offset for post-rounding.
     *
     * @deprecated Option "post_offset" is deprecated as of ES 1.5. Use "offset" instead
     *
     * @param string $offset "1d", for example
     *
     * @return $this
     */
    public function setPostOffset($offset)
    {
        return $this->setParam('post_offset', $offset);
    }

    /**
     * Set offset option.
     *
     * @param  string
     *
     * @return $this
     */
    public function setOffset($offset)
    {
        return $this->setParam('offset', $offset);
    }

    /**
     * Set the format for returned bucket key_as_string values.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/master/search-aggregations-bucket-daterange-aggregation.html#date-format-pattern
     *
     * @param string $format see link for formatting options
     *
     * @return $this
     */
    public function setFormat($format)
    {
        return $this->setParam('format', $format);
    }
}
