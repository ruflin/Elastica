<?php
namespace Elastica\Aggregation;

/**
 * Class DateRange.
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-daterange-aggregation.html
 */
class DateRange extends Range
{
    /**
     * Set the formatting for the returned date values.
     *
     * @param string $format see documentation for formatting options
     *
     * @return $this
     */
    public function setFormat($format)
    {
        return $this->setParam('format', $format);
    }
}
