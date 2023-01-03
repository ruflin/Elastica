<?php

namespace Elastica\Aggregation;

/**
 * Class BucketSelector.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-pipeline-bucket-selector-aggregation.html
 */
class BucketSelector extends AbstractSimpleAggregation implements GapPolicyInterface
{
    use Traits\GapPolicyTrait;

    public function __construct(string $name, array $bucketsPath, string $script)
    {
        parent::__construct($name);

        $this->setBucketsPath($bucketsPath);
        $this->setScript($script);
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
}
