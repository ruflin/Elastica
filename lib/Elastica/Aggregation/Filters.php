<?php

namespace Elastica\Aggregation;

use Elastica\Filter\AbstractFilter;

/**
 * Class Filters
 * @package Elastica\Aggregation
 * @link http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/search-aggregations-bucket-filters-aggregation.html
 */
class Filters extends AbstractAggregation
{
    /**
     * Add a filter
     *
     * If a name is given, it will be added as a key, otherwise considered as an anonymous filter
     *
     * @param  AbstractFilter $filter
     * @param  string         $name
     * @return Filters
     */
    public function addFilter(AbstractFilter $filter, $name = '')
    {
        if (empty($name)) {
            $filterArray[] = $filter->toArray();
        } else {
            $filterArray[$name] = $filter->toArray();
        }

        return $this->addParam('filters', $filterArray);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $array   = array();
        $filters = $this->getParam('filters');

        foreach ($filters as $filter) {
            // Detect between anonymous filters and named ones
            $key = key($filter);

            if (is_string($key)) {
                $array['filters']['filters'][$key] = current($filter);
            } else {
                $array['filters']['filters'][] = current($filter);
            }
        }

        if ($this->_aggs) {
            $array['aggs'] = $this->_aggs;
        }

        return $array;
    }
}
