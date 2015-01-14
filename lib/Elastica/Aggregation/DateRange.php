<?php

namespace Elastica\Aggregation;

/**
 * Class DateRange
 * @package Elastica\Aggregation
 * @link http://www.elasticsearch.org/guide/en/elasticsearch/reference/master/search-aggregations-bucket-daterange-aggregation.html
 */
class DateRange extends Range
{
    /**
     * Set the formatting for the returned date values
     * @param  string $format see documentation for formatting options
     * @return Range
     */
    public function setFormat($format)
    {
        return $this->setParam('format', $format);
    }
}
