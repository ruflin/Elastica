<?php

declare(strict_types=1);

namespace Elastica\Aggregation\Traits;

trait ShardSizeTrait
{
    /**
     * @return $this
     */
    public function setShardSize(int $size): self
    {
        return $this->setParam('shard_size', $size);
    }
}
