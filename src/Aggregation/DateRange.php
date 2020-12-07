<?php

namespace Elastica\Aggregation;

/**
 * Class DateRange.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-daterange-aggregation.html
 */
class DateRange extends Range
{
    use Traits\MissingTrait;

    /**
     * Set the formatting for the returned date values.
     *
     * @param string $format see documentation for formatting options
     *
     * @return $this
     */
    public function setFormat(string $format): self
    {
        return $this->setParam('format', $format);
    }

    /**
     * Set time zone.
     */
    public function setTimezone(string $timezone): self
    {
        return $this->setParam('time_zone', $timezone);
    }
}
