<?php

namespace Elastica\Aggregation;

/**
 * Implements a Extended Stats Bucket Aggregation.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-pipeline-extended-stats-bucket-aggregation.html
 */
class ExtendedStatsBucket extends AbstractAggregation implements GapPolicyInterface
{
    use Traits\BucketsPathTrait;
    use Traits\GapPolicyTrait;

    public function __construct(string $name, string $bucketsPath)
    {
        parent::__construct($name);

        $this->setBucketsPath($bucketsPath);
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
