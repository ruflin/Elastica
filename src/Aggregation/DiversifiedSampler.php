<?php

namespace Elastica\Aggregation;

/**
 * Class DiversifiedSampler.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-diversified-sampler-aggregation.html
 */
class DiversifiedSampler extends AbstractSimpleAggregation
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

    /**
     * Set the maximum number of documents to be returned per value.
     *
     * @return $this
     */
    public function setMaxDocsPerValue(int $max): self
    {
        return $this->setParam('max_docs_per_value', $max);
    }

    /**
     * Instruct Elasticsearch to use direct field data or ordinals/hashes of the field values to execute this aggregation.
     * The execution hint will be ignored if it is not applicable.
     *
     * @return $this
     */
    public function setExecutionHint(string $hint): self
    {
        return $this->setParam('execution_hint', $hint);
    }
}
