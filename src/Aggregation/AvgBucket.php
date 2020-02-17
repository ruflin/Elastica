<?php

namespace Elastica\Aggregation;

use Elastica\Exception\InvalidException;

/**
 * Class AvgBucket.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-pipeline-avg-bucket-aggregation.html
 */
class AvgBucket extends AbstractAggregation
{
    public const DEFAULT_GAP_POLICY_VALUE = 'skip';
    public const DEFAULT_FORMAT_VALUE = null;

    public function __construct(string $name, ?string $bucketsPath = null)
    {
        parent::__construct($name);

        if (null !== $bucketsPath) {
            $this->setBucketsPath($bucketsPath);
        }
    }

    /**
     * Set the buckets_path for this aggregation.
     *
     * @return $this
     */
    public function setBucketsPath(string $bucketsPath): self
    {
        return $this->setParam('buckets_path', $bucketsPath);
    }

    /**
     * Set the gap policy for this aggregation.
     *
     * @return $this
     */
    public function setGapPolicy(string $gapPolicy): self
    {
        return $this->setParam('gap_policy', $gapPolicy);
    }

    /**
     * Set the format for this aggregation.
     *
     * @return $this
     */
    public function setFormat(?string $format = null): self
    {
        return $this->setParam('format', $format);
    }

    /**
     * @throws InvalidException If buckets path or script is not set
     */
    public function toArray(): array
    {
        if (!$this->hasParam('buckets_path')) {
            throw new InvalidException('Buckets path is required');
        }

        return parent::toArray();
    }
}
