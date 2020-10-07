<?php

namespace Elastica\Aggregation;

use Elastica\Exception\InvalidException;

/**
 * Class PercentilesBucket.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-pipeline-percentiles-bucket-aggregation.html
 */
class PercentilesBucket extends AbstractAggregation
{
    /**
     * @param string      $name        the name of this aggregation
     * @param string|null $bucketsPath the field on which to perform this aggregation
     */
    public function __construct(string $name, ?string $bucketsPath = null)
    {
        parent::__construct($name);

        if (null !== $bucketsPath) {
            $this->setBucketsPath($bucketsPath);
        }
    }

    /**
     * @throws InvalidException If buckets path or script is not set
     */
    public function toArray(): array
    {
        if (!$this->hasParam('buckets_path')) {
            throw new InvalidException('Buckets path is required');
        }

        return parent::toArray();
    }

    /**
     * Set the buckets_path for this aggregation.
     */
    public function setBucketsPath(string $bucketsPath): PercentilesBucket
    {
        return $this->setParam('buckets_path', $bucketsPath);
    }

    /**
     * Set the gap policy for this aggregation.
     */
    public function setGapPolicy(string $gapPolicy): PercentilesBucket
    {
        return $this->setParam('gap_policy', $gapPolicy);
    }

    /**
     * Set the format for this aggregation.
     */
    public function setFormat(string $format): PercentilesBucket
    {
        return $this->setParam('format', $format);
    }

    /**
     * Set which percents must be returned.
     *
     * @param float[] $percents
     */
    public function setPercents(array $percents): PercentilesBucket
    {
        return $this->setParam('percents', $percents);
    }

    /**
     * Set keyed flag to return the range as an hash instead of an array of key-value pairs.
     */
    public function setKeyed(bool $keyed): PercentilesBucket
    {
        return $this->setParam('keyed', $keyed);
    }
}
