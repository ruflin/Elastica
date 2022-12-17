<?php

namespace Elastica\Aggregation;

/**
 * Class AvgBucket.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-pipeline-avg-bucket-aggregation.html
 */
class AvgBucket extends AbstractAggregation implements GapPolicyInterface
{
    use Traits\BucketsPathTrait;
    use Traits\GapPolicyTrait;

    public const DEFAULT_FORMAT_VALUE = null;

    public function __construct(string $name, string $bucketsPath)
    {
        parent::__construct($name);

        $this->setBucketsPath($bucketsPath);
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
