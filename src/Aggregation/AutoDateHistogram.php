<?php

namespace Elastica\Aggregation;

use Elastica\Aggregation\Traits\MissingTrait;

class AutoDateHistogram extends AbstractSimpleAggregation
{
    use MissingTrait;

    public function __construct(string $name, string $field)
    {
        parent::__construct($name);
        $this->setField($field);
    }

    /**
     * A target number of buckets.
     * The buckets field is optional, and will default to 10 buckets if not specified.
     *
     * @return $this
     */
    public function setBuckets(int $buckets): self
    {
        return $this->setParam('buckets', $buckets);
    }

    /**
     * Set the format for this aggregation.
     * If no format is specified, then it will use the first date format specified in the field mapping.
     *
     * @return $this
     */
    public function setFormat(string $format): self
    {
        return $this->setParam('format', $format);
    }

    /**
     * Set time_zone option.
     * The time_zone parameter can be used to indicate that bucketing should use a different time zone.
     *
     * @return $this
     */
    public function setTimezone(string $timezone): self
    {
        return $this->setParam('time_zone', $timezone);
    }

    /**
     * The minimum_interval allows the caller to specify the minimum rounding interval that should be used.
     * The accepted units: year, month, day, hour, minute, second.
     *
     * @return $this
     */
    public function setMinimumInterval(string $minimumInterval): self
    {
        return $this->setParam('minimum_interval', $minimumInterval);
    }
}
