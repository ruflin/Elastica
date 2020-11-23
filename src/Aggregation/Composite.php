<?php

namespace Elastica\Aggregation;

class Composite extends AbstractAggregation
{
    /**
     * @return $this
     */
    public function setSize(int $size): self
    {
        return $this->setParam('size', $size);
    }

    /**
     * @return $this
     */
    public function addSource(AbstractAggregation $aggregation): self
    {
        return $this->addParam('sources', [$aggregation]);
    }

    /**
     * @return $this
     */
    public function addAfter(?array $checkpoint): self
    {
        if (null === $checkpoint) {
            return $this;
        }

        return $this->setParam('after', $checkpoint);
    }
}
