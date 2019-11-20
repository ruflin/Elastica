<?php

namespace Elastica\Aggregation;

use Elastica\Query\AbstractQuery;

class AdjacencyMatrix extends AbstractAggregation
{
    /**
     * Add a named filter.
     *
     * @return $this
     */
    public function addFilter(AbstractQuery $filter, string $name): self
    {
        $filterArray = [];
        $filterArray[$name] = $filter;

        return $this->addParam('filters', $filterArray);
    }

    /**
     * @return $this
     */
    public function setSeparator(string $separator = '&'): self
    {
        return $this->setParam('separator', $separator);
    }

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
