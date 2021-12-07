<?php

namespace Elastica\Aggregation;

/**
 * Class CumulativeSum.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-pipeline-cumulative-sum-aggregation.html
 */
class CumulativeSum extends AbstractAggregation
{
    use Traits\BucketsPathTrait;

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
