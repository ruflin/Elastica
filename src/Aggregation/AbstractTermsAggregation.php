<?php

namespace Elastica\Aggregation;

/**
 * Class AbstractTermsAggregation.
 */
abstract class AbstractTermsAggregation extends AbstractSimpleAggregation
{
    /**
     * Set the minimum number of documents in which a term must appear in order to be returned in a bucket.
     *
     * @return $this
     */
    public function setMinimumDocumentCount(int $count): self
    {
        return $this->setParam('min_doc_count', $count);
    }

    /**
     * Filter documents to include based on a regular expression.
     *
     * @param string $pattern a regular expression
     * @param string $flags   Java Pattern flags
     *
     * @return $this
     */
    public function setInclude(string $pattern, ?string $flags = null): self
    {
        if (null === $flags) {
            return $this->setParam('include', $pattern);
        }

        return $this->setParam('include', [
            'pattern' => $pattern,
            'flags' => $flags,
        ]);
    }

    /**
     * Filter documents to exclude based on a regular expression.
     *
     * @param string $pattern a regular expression
     * @param string $flags   Java Pattern flags
     *
     * @return $this
     */
    public function setExclude(string $pattern, ?string $flags = null): self
    {
        if (null === $flags) {
            return $this->setParam('exclude', $pattern);
        }

        return $this->setParam('exclude', [
            'pattern' => $pattern,
            'flags' => $flags,
        ]);
    }

    /**
     * Sets the amount of terms to be returned.
     *
     * @param int $size the amount of terms to be returned
     *
     * @return $this
     */
    public function setSize(int $size): self
    {
        return $this->setParam('size', $size);
    }

    /**
     * Sets how many terms the coordinating node will request from each shard.
     *
     * @param int $shardSize the amount of terms to be returned
     *
     * @return $this
     */
    public function setShardSize(int $shardSize): self
    {
        return $this->setParam('shard_size', $shardSize);
    }

    /**
     * Instruct Elasticsearch to use direct field data or ordinals of the field values to execute this aggregation.
     * The execution hint will be ignored if it is not applicable.
     *
     * @param string $hint map or ordinals
     *
     * @return $this
     */
    public function setExecutionHint(string $hint): self
    {
        return $this->setParam('execution_hint', $hint);
    }
}
