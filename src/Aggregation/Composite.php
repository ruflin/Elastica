<?php

namespace Elastica\Aggregation;

class Composite extends AbstractAggregation
{
    /**
     * @param int $size
     * @return $this
     */
    public function setSize(int $size): self
    {
        return $this->setParam('size', $size);
    }

    /**
     * @param AbstractAggregation $aggregation
     * @return $this
     */
    public function addSource(AbstractAggregation $aggregation): self
    {
        return $this->addParam('sources', [$aggregation]);
    }

    /**
     * @param array|null $checkpoint
     * @return $this
     */
    public function addAfter(?array $checkpoint): self
    {
        return $this->setParam('after', $checkpoint);
    }
}
