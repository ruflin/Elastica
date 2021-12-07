<?php

namespace Elastica\Aggregation;

use Elastica\Exception\InvalidException;

/**
 * Class SerialDiff.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-pipeline-serialdiff-aggregation.html
 */
class SerialDiff extends AbstractAggregation implements GapPolicyInterface
{
    use Traits\BucketsPathTrait;

    public const DEFAULT_GAP_POLICY_VALUE = GapPolicyInterface::INSERT_ZEROS;

    public function __construct(string $name, ?string $bucketsPath = null)
    {
        parent::__construct($name);

        if (null !== $bucketsPath) {
            $this->setBucketsPath($bucketsPath);
        } elseif (\func_num_args() >= 2) {
            \trigger_deprecation('ruflin/elastica', '7.1.3', 'Passing null as 2nd argument to "%s()" is deprecated, pass a string instead. It will be removed in 8.0.', __METHOD__);
        } else {
            \trigger_deprecation('ruflin/elastica', '7.1.3', 'Not passing a 2nd argument to "%s()" is deprecated, pass a string instead. It will be removed in 8.0.', __METHOD__);
        }
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

    /**
     * @throws InvalidException If buckets path is not set
     */
    public function toArray(): array
    {
        if (!$this->hasParam('buckets_path')) {
            throw new InvalidException('Buckets path is required');
        }

        return parent::toArray();
    }
}
