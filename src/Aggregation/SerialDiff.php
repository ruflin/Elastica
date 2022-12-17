<?php

namespace Elastica\Aggregation;

/**
 * Class SerialDiff.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-pipeline-serialdiff-aggregation.html
 */
class SerialDiff extends AbstractAggregation implements GapPolicyInterface
{
    use Traits\BucketsPathTrait;

    public const DEFAULT_GAP_POLICY_VALUE = GapPolicyInterface::INSERT_ZEROS;

    public function __construct(string $name, string $bucketsPath)
    {
        parent::__construct($name);

        $this->setBucketsPath($bucketsPath);
    }

    /**
     * Set the lag for this aggregation.
     *
     * @return $this
     */
    public function setLag(int $lag = 1): self
    {
        return $this->setParam('lag', $lag);
    }

    /**
     * Set the gap policy for this aggregation.
     *
     * @return $this
     */
    public function setGapPolicy(string $gapPolicy = self::DEFAULT_GAP_POLICY_VALUE): self
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
}
