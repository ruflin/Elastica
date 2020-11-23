<?php

namespace Elastica\Aggregation;

/**
 * Class DateHistogram.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-datehistogram-aggregation.html
 */
class DateHistogram extends AbstractSimpleAggregation
{
    use Traits\KeyedTrait;
    use Traits\MissingTrait;

    public const DEFAULT_TIMEZONE_VALUE = 'UTC';

    /**
     * @param string     $name     the name of this aggregation
     * @param string     $field    the name of the field on which to perform the aggregation
     * @param int|string $interval the interval by which documents will be bucketed
     */
    public function __construct(string $name, string $field, $interval = null)
    {
        parent::__construct($name, $field);
        $this->setField($field);

        if (null !== $interval) {
            trigger_deprecation('ruflin/elastica', '7.1.0', 'Argument 3 passed to "%s()" is deprecated, use "setDateInterval()" or "setCalendarInterval()" instead. It will be removed in 8.0.', __METHOD__);

            $this->setParam('interval', $interval);
        }
    }

    /**
     * Set the interval by which documents will be bucketed.
     *
     * @deprecated Deprecated since 7.1.0
     *
     * @param int|string $interval
     *
     * @return $this
     */
    public function setInterval($interval): self
    {
        trigger_deprecation('ruflin/elastica', '7.1.0', 'The "%s()" method is deprecated, use "setDateInterval()" or "setCalendarInterval()" instead. It will be removed in 8.0.', __METHOD__);

        return $this->setParam('interval', $interval);
    }

    /**
     * Set the fixed interval by which documents will be bucketed.
     *
     * @param int|string $interval
     *
     * @return $this
     */
    public function setFixedInterval($interval): self
    {
        return $this->setParam('fixed_interval', $interval);
    }

    /**
     * Set the calendar interval by which documents will be bucketed.
     *
     * @param int|string $interval
     *
     * @return $this
     */
    public function setCalendarInterval($interval): self
    {
        return $this->setParam('calendar_interval', $interval);
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

    /**
     * Set the bucket sort order.
     *
     * @param string $order     "_count", "_term", or the name of a sub-aggregation or sub-aggregation response field
     * @param string $direction "asc" or "desc"
     *
     * @return $this
     */
    public function setOrder(string $order, string $direction): self
    {
        return $this->setParam('order', [$order => $direction]);
    }

    /**
     * Set the minimum number of documents which must fall into a bucket in order for the bucket to be returned.
     *
     * @param int $count set to 0 to include empty buckets
     *
     * @return $this
     */
    public function setMinimumDocumentCount(int $count): self
    {
        return $this->setParam('min_doc_count', $count);
    }
}
