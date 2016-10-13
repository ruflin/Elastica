<?php
namespace Elastica\Aggregation;

use Elastica\Exception\InvalidException;
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
    public function __construct($name, AbstractQuery $filter = null)
    {
        parent::__construct($name);

        if ($filter !== null) {
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
    public function setFilter(AbstractQuery $filter)
    {
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

        $array = [
            'filter' => $this->getParam('filter')->toArray(),
        ];

        if ($this->_aggs) {
            $array['aggs'] = $this->_convertArrayable($this->_aggs);
        }

        return $array;
    }
}
