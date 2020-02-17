<?php

namespace Elastica\Aggregation;

/**
 * Class Sampler.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-sampler-aggregation.html
 */
class Sampler extends AbstractAggregation
{
    /**
     * Set the number of top-scoring documents to be returned from each shard.
     *
     * @return $this
     */
    public function setShardSize(int $shardSize): self
    {
        return $this->setParam('shard_size', $shardSize);
    }
}
