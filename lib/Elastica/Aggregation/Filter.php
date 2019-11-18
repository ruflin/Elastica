<?php

namespace Elastica\Aggregation;

use Elastica\Exception\InvalidException;
use Elastica\Query\AbstractQuery;

/**
 * Class Filter.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-filter-aggregation.html
 */
class Filter extends AbstractAggregation
{
    public function __construct(string $name, ?AbstractQuery $filter = null)
    {
        parent::__construct($name);

        if (null !== $filter) {
            $this->setFilter($filter);
        }
    }

    /**
     * Set the filter for this aggregation.
     *
     * @return $this
     */
    public function setFilter(AbstractQuery $filter): self
    {
        return $this->setParam('filter', $filter);
    }

    /**
     * @throws \Elastica\Exception\InvalidException If filter is not set
     */
    public function toArray(): array
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
