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
        return $this->addParam('filters', $filter, $name);
    }

    /**
     * @return $this
     */
    public function setSeparator(string $separator = '&'): self
    {
        return $this->setParam('separator', $separator);
    }
}
