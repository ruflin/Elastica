<?php

namespace Elastica\Aggregation;

/**
 * Class BucketSelector.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-pipeline-bucket-selector-aggregation.html
 */
class BucketSelector extends AbstractSimpleAggregation
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
     * Set the gap policy for this aggregation.
     *
     * @return $this
     */
    public function setGapPolicy(string $gapPolicy = 'skip'): self
    {
        return $this->setParam('gap_policy', $gapPolicy);
    }
}
