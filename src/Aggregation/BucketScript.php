<?php

namespace Elastica\Aggregation;

use Elastica\Exception\InvalidException;

/**
 * Class BucketScript.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-pipeline-bucket-script-aggregation.html
 */
class BucketScript extends AbstractAggregation
{
    public function __construct(string $name, ?array $bucketsPath = null, ?string $script = null)
    {
        parent::__construct($name);

        if (null !== $bucketsPath) {
            $this->setBucketsPath($bucketsPath);
        }

        if (null !== $script) {
            $this->setScript($script);
        }
    }

    /**
     * Set the buckets_path for this aggregation.
     *
     * @return $this
     */
    public function setBucketsPath(array $bucketsPath): self
    {
        return $this->setParam('buckets_path', $bucketsPath);
    }

    /**
     * Set the script for this aggregation.
     *
     * @return $this
     */
    public function setScript(string $script): self
    {
        return $this->setParam('script', $script);
    }

    /**
     * Set the gap policy for this aggregation.
     *
     * @return $this
     */
    public function setGapPolicy(string $gapPolicy = 'skip'): self
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
        if (!$this->hasParam('script')) {
            throw new InvalidException('Script parameter is required');
        }

        return parent::toArray();
    }
}
