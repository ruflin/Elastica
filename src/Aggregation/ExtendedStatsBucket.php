<?php

namespace Elastica\Aggregation;

/**
 * Implements a Extended Stats Bucket Aggregation.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-pipeline-extended-stats-bucket-aggregation.html
 */
class ExtendedStatsBucket extends AbstractAggregation
{
    public function __construct(string $name, string $bucketsPath)
    {
        parent::__construct($name);
        $this->setBucketsPath($bucketsPath);
    }

    public function setBucketsPath(string $bucketsPath): self
    {
        return $this->setParam('buckets_path', $bucketsPath);
    }

    public function setGapPolicy(string $gapPolicy): self
    {
        return $this->setParam('gap_policy', $gapPolicy);
    }

    public function setFormat(string $format): self
    {
        return $this->setParam('format', $format);
    }

    public function setSigma(int $sigma): self
    {
        return $this->setParam('sigma', $sigma);
    }
}
