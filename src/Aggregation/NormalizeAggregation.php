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
    public function __construct(string $name, ?string $bucketsPath = null, ?string $method = null)
    {
        parent::__construct($name);

        if (null !== $bucketsPath) {
            $this->setBucketsPath($bucketsPath);
        }

        if (null !== $method) {
            $this->setMethod($method);
        }
    }

    /**
     * Set the buckets_path for this aggregation.
     *
     * @return $this
     */
    public function setBucketsPath(string $bucketsPath): self
    {
        return $this->setParam('buckets_path', $bucketsPath);
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
