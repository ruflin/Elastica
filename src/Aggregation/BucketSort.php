<?php

declare(strict_types=1);

namespace Elastica\Aggregation;

use Elastica\Exception\InvalidException;

class BucketSort extends AbstractAggregation implements GapPolicyInterface
{
    use Traits\GapPolicyTrait;

    public function toArray(): array
    {
        if (!$this->hasParam('sort') && !$this->hasParam('size') && !$this->hasParam('from')) {
            throw new InvalidException('Either the sort param, the size param or the from param should be set');
        }

        return parent::toArray();
    }

    /**
     * The number of buckets to return. Defaults to all buckets of the parent aggregation.
     *
     * @return $this
     */
    public function setSize(int $size): self
    {
        return $this->setParam('size', $size);
    }

    /**
     * Buckets in positions prior to the set value will be truncated.
     *
     * @return $this
     */
    public function setFrom(int $from): self
    {
        return $this->setParam('from', $from);
    }

    /**
     * How the top matching hits should be sorted. By default the hits are sorted by the score of the main query.
     *
     * @param string $aggregationName the name of an aggregation
     * @param string $direction       "asc" or "desc"
     */
    public function addSort(string $aggregationName, string $direction): self
    {
        return $this->addParam('sort', [$aggregationName => ['order' => $direction]]);
    }
}
