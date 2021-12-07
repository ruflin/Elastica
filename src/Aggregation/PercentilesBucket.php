<?php

namespace Elastica\Aggregation;

use Elastica\Exception\InvalidException;

/**
 * Class PercentilesBucket.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-pipeline-percentiles-bucket-aggregation.html
 */
class PercentilesBucket extends AbstractAggregation implements GapPolicyInterface
{
    use Traits\BucketsPathTrait;
    use Traits\GapPolicyTrait;
    use Traits\KeyedTrait;

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
     * Set the format for this aggregation.
     */
    public function setFormat(string $format): self
    {
        return $this->setParam('format', $format);
    }

    /**
     * Set which percents must be returned.
     *
     * @param float[] $percents
     */
    public function setPercents(array $percents): self
    {
        return $this->setParam('percents', $percents);
    }
}
