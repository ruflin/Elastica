<?php

namespace Elastica\Aggregation;

use Elastica\Exception\InvalidException;
use Elastica\Query\AbstractQuery;

class AdjacencyMatrix extends AbstractAggregation
{
    /**
     * Add a named filter.
     *
     * @param AbstractQuery $filter
     * @param string        $name
     *
     * @return $this
     */
    public function addFilter(AbstractQuery $filter, $name): self
    {
        if (!\is_string($name)) {
            throw new InvalidException('Name must be a string');
        }

        $filterArray = [];
        $filterArray[$name] = $filter;

        return $this->addParam('filters', $filterArray);
    }

    /**
     * @param string $separator
     *
     * @return $this
     */
    public function setSeparator(string $separator = '&'): self
    {
        return $this->setParam('separator', $separator);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $array = [];
        $filters = $this->getParam('filters');

        foreach ($filters as $filter) {
            $key = \key($filter);
            $array['adjacency_matrix']['filters'][$key] = \current($filter)->toArray();
        }

        if ($this->hasParam('separator')) {
            $array['adjacency_matrix']['separator'] = $this->getParam('separator');
        }

        if ($this->_aggs) {
            $array['aggs'] = $this->_convertArrayable($this->_aggs);
        }

        return $array;
    }
}
