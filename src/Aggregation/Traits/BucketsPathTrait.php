<?php

declare(strict_types=1);

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
