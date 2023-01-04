<?php

namespace Elastica\Aggregation;

use Elastica\Exception\InvalidException;

/**
 * Class BucketScript.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-pipeline-bucket-script-aggregation.html
 */
class BucketScript extends AbstractAggregation implements GapPolicyInterface
{
    use Traits\GapPolicyTrait;

    public function __construct(string $name, ?array $bucketsPath = null, ?string $script = null)
    {
        parent::__construct($name);

        if (null !== $bucketsPath) {
            $this->setBucketsPath($bucketsPath);
        } elseif (\func_num_args() >= 2) {
            \trigger_deprecation('ruflin/elastica', '7.4.0', 'Passing null as 2nd argument to "%s()" is deprecated, pass an array instead. It will be mandatory in 8.0.', __METHOD__);
        } else {
            \trigger_deprecation('ruflin/elastica', '7.4.0', 'Not passing a 2nd argument to "%s()" is deprecated, pass an array instead. It will be mandatory in 8.0.', __METHOD__);
        }

        if (null !== $script) {
            $this->setScript($script);
        } elseif (\func_num_args() >= 3) {
            \trigger_deprecation('ruflin/elastica', '7.4.0', 'Passing null as 3rd argument to "%s()" is deprecated, pass a string instead. It will be mandatory in 8.0.', __METHOD__);
        } else {
            \trigger_deprecation('ruflin/elastica', '7.4.0', 'Not passing a 3rd argument to "%s()" is deprecated, pass a string instead. It will be mandatory in 8.0.', __METHOD__);
        }
    }

    /**
     * Set the buckets_path for this aggregation.
     *
     * @return $this
     */
    public function setBucketsPath(array $bucketsPath): self
    {
        return $this->setParam('buckets_path', $bucketsPath);
    }

    /**
     * Set the script for this aggregation.
     *
     * @return $this
     */
    public function setScript(string $script): self
    {
        return $this->setParam('script', $script);
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
     * @throws InvalidException If buckets path or script is not set
     */
    public function toArray(): array
    {
        if (!$this->hasParam('buckets_path')) {
            throw new InvalidException('Buckets path is required');
        }
        if (!$this->hasParam('script')) {
            throw new InvalidException('Script parameter is required');
        }

        return parent::toArray();
    }
}
