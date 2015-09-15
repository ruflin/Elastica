<?php
namespace Elastica\Aggregation;

use Elastica\Filter\AbstractFilter;

/**
 * Class Filters.
 *
 * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-filters-aggregation.html
 */
class Filters extends AbstractAggregation
{
    /**
     * Add a filter.
     *
     * If a name is given, it will be added as a key, otherwise considered as an anonymous filter
     *
     * @param AbstractFilter $filter
     * @param string         $name
     *
     * @return $this
     */
    public function addFilter(AbstractFilter $filter, $name = '')
    {
        $filterArray = array();

        if (is_string($name)) {
            $filterArray[$name] = $filter;
        } else {
            $filterArray[] = $filter;
        }

        return $this->addParam('filters', $filterArray);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $array = array();
        $filters = $this->getParam('filters');

        foreach ($filters as $filter) {
            // Detect between anonymous filters and named ones
            $key = key($filter);

            if (is_string($key) && !empty($key)) {
                $array['filters']['filters'][$key] = current($filter)->toArray();
            } else {
                $array['filters']['filters'][] = current($filter)->toArray();
            }
        }

        if ($this->_aggs) {
            $array['aggs'] = $this->_convertArrayable($this->_aggs);
        }

        return $array;
    }
}
