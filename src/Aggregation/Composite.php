<?php

declare(strict_types=1);

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
     * @param array<string, mixed>|null $checkpoint
     *
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
