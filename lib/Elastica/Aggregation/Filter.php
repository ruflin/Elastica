<?php

namespace Elastica\Aggregation;

use Elastica\Exception\InvalidException;
use Elastica\Filter\AbstractFilter;
use Elastica\Query\AbstractQuery;

/**
 * Class Filter.
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-filter-aggregation.html
 */
class Filter extends AbstractAggregation
{
    /**
     * @param string        $name
     * @param AbstractQuery $filter
     */
    public function __construct($name, $filter = null)
    {
        parent::__construct($name);

        if ($filter !== null) {
            if ($filter instanceof AbstractFilter) {
                trigger_error('Deprecated: Elastica\Aggregation\Filter passing filter as AbstractFilter is deprecated. Pass instance of AbstractQuery instead.', E_USER_DEPRECATED);
            } elseif (!($filter instanceof AbstractQuery)) {
                throw new InvalidException('Filter must be instance of AbstractQuery');
            }

            $this->setFilter($filter);
        }
    }

    /**
     * Set the filter for this aggregation.
     *
     * @param AbstractQuery $filter
     *
     * @return $this
     */
    public function setFilter($filter)
    {
        if ($filter instanceof AbstractFilter) {
            trigger_error('Deprecated: Elastica\Aggregation\Filter\setFilter() passing filter as AbstractFilter is deprecated. Pass instance of AbstractQuery instead.', E_USER_DEPRECATED);
        } elseif (!($filter instanceof AbstractQuery)) {
            throw new InvalidException('Filter must be instance of AbstractQuery');
        }

        return $this->setParam('filter', $filter);
    }

    /**
     * @throws \Elastica\Exception\InvalidException If filter is not set
     *
     * @return array
     */
    public function toArray()
    {
        if (!$this->hasParam('filter')) {
            throw new InvalidException('Filter is required');
        }

        $array = array(
            'filter' => $this->getParam('filter')->toArray(),
        );

        if ($this->_aggs) {
            $array['aggs'] = $this->_convertArrayable($this->_aggs);
        }

        return $array;
    }
}
