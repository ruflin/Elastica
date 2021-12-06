<?php

namespace Elastica\Aggregation\Traits;

trait BucketsPathTrait
{
    /**
     * @return $this
     */
    public function setBucketsPath(string $bucketsPath): self
    {
        return $this->setParam('buckets_path', $bucketsPath);
    }
}
