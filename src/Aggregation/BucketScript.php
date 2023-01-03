<?php

namespace Elastica\Aggregation;

/**
 * Class BucketScript.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-pipeline-bucket-script-aggregation.html
 */
class BucketScript extends AbstractAggregation implements GapPolicyInterface
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
     * Set the format for this aggregation.
     *
     * @return $this
     */
    public function setFormat(?string $format = null): self
    {
        return $this->setParam('format', $format);
    }
}
