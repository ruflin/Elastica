<?php

namespace Elastica\Aggregation;

/**
 * Class DateHistogram.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-datehistogram-aggregation.html
 */
class DateHistogram extends Histogram
{
    public const DEFAULT_TIMEZONE_VALUE = 'UTC';

    private const CALENDAR_INTERVAL_VALUES = [
        '1m',
        'minute',
        '1h',
        'hour',
        '1d',
        'day',
        '1w',
        'week',
        '1M',
        'month',
        '1q',
        'quarter',
        '1y',
        'year',
    ];

    /**
     * Set the interval by which documents will be bucketed.
     *
     * @param int|string $interval
     *
     * @return $this
     */
    public function setInterval($interval)
    {
        $interval = (string) $interval;

        if (\in_array($interval, self::CALENDAR_INTERVAL_VALUES, true)) {
            return $this->setCalendarInterval($interval);
        }

        return $this->setFixedInterval($interval);
    }

    /**
     * @return $this
     */
    public function setCalendarInterval(string $interval): self
    {
        return $this->setParam('calendar_interval', $interval);
    }

    /**
     * @return $this
     */
    public function setFixedInterval(string $interval): self
    {
        return $this->setParam('fixed_interval', $interval);
    }

    /**
     * Set time_zone option.
     *
     * @return $this
     */
    public function setTimezone(string $timezone): self
    {
        return $this->setParam('time_zone', $timezone);
    }

    /**
     * Adjust for granularity of date data.
     *
     * @param int $factor set to 1000 if date is stored in seconds rather than milliseconds
     *
     * @return $this
     */
    public function setFactor(int $factor): self
    {
        return $this->setParam('factor', $factor);
    }

    /**
     * Set offset option.
     *
     * @return $this
     */
    public function setOffset(string $offset): self
    {
        return $this->setParam('offset', $offset);
    }

    /**
     * Set the format for returned bucket key_as_string values.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-daterange-aggregation.html#date-format-pattern
     *
     * @param string $format see link for formatting options
     *
     * @return $this
     */
    public function setFormat(string $format): self
    {
        return $this->setParam('format', $format);
    }

    /**
     * Set extended bounds option.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-histogram-aggregation.html#search-aggregations-bucket-histogram-aggregation-extended-bounds
     *
     * @param string $min see link for formatting options
     * @param string $max see link for formatting options
     *
     * @return $this
     */
    public function setExtendedBounds(string $min = '', string $max = ''): self
    {
        $bounds = [];
        $bounds['min'] = $min;
        $bounds['max'] = $max;
        // switch if min is higher then max
        if (\strtotime($min) > \strtotime($max)) {
            $bounds['min'] = $max;
            $bounds['max'] = $min;
        }

        return $this->setParam('extended_bounds', $bounds);
    }
}
