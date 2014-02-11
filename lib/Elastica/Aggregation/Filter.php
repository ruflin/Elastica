<?php

namespace Elastica\Aggregation;


use Elastica\Filter\AbstractFilter;

/**
 * Class Filter
 * @package Elastica\Aggregation
 * @link http://www.elasticsearch.org/guide/en/elasticsearch/reference/master/search-aggregations-bucket-filter-aggregation.html
 */
class Filter extends AbstractAggregation
{
    /**
     * Set the filter for this aggregation
     * @param AbstractFilter $filter
     * @return Filter
     */
    public function setFilter(AbstractFilter $filter)
    {
        return $this->setParam("filter", $filter->toArray());
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array(
            "filter" => $this->getParam("filter"),
            "aggs" => $this->_aggs
        );
    }
}