<?php

namespace Elastica\Aggregation;

/**
 * Class StatsBucket.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-pipeline-stats-bucket-aggregation.html
 */
class StatsBucket extends AbstractAggregation implements GapPolicyInterface
{
    use Traits\BucketsPathTrait;
    use Traits\GapPolicyTrait;

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
    public function setFormat(string $format): self
    {
        return $this->setParam('format', $format);
    }
}
