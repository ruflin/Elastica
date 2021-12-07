<?php

namespace Elastica\Aggregation;

use Elastica\Exception\InvalidException;

/**
 * Class NormalizeAggregation.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-pipeline-normalize-aggregation.html
 */
class NormalizeAggregation extends AbstractAggregation
{
    use Traits\BucketsPathTrait;

    public function __construct(string $name, ?string $bucketsPath = null, ?string $method = null)
    {
        parent::__construct($name);

        if (null !== $bucketsPath) {
            $this->setBucketsPath($bucketsPath);
        } elseif (\func_num_args() >= 2) {
            \trigger_deprecation('ruflin/elastica', '7.1.3', 'Passing null as 2nd argument to "%s()" is deprecated, pass a string instead. It will be removed in 8.0.', __METHOD__);
        } else {
            \trigger_deprecation('ruflin/elastica', '7.1.3', 'Not passing a 2nd argument to "%s()" is deprecated, pass a string instead. It will be removed in 8.0.', __METHOD__);
        }

        if (null !== $method) {
            $this->setMethod($method);
        } elseif (\func_num_args() >= 3) {
            \trigger_deprecation('ruflin/elastica', '7.1.3', 'Passing null as 3rd argument to "%s()" is deprecated, pass a string instead. It will be removed in 8.0.', __METHOD__);
        } else {
            \trigger_deprecation('ruflin/elastica', '7.1.3', 'Not passing a 3rd argument to "%s()" is deprecated, pass a string instead. It will be removed in 8.0.', __METHOD__);
        }
    }

    /**
     * Set the method for this aggregation.
     *
     * @return $this
     */
    public function setMethod(string $method): self
    {
        return $this->setParam('method', $method);
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

    /**
     * @throws InvalidException If buckets path or method are not set
     */
    public function toArray(): array
    {
        if (!$this->hasParam('buckets_path')) {
            throw new InvalidException('Buckets path is required');
        }

        if (!$this->hasParam('method')) {
            throw new InvalidException('Method parameter is required');
        }

        return parent::toArray();
    }
}
