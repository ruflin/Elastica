<?php

namespace Elastica\Aggregation;

/**
 * Class NormalizeAggregation.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-pipeline-normalize-aggregation.html
 */
class NormalizeAggregation extends AbstractAggregation
{
    use Traits\BucketsPathTrait;

    public function __construct(string $name, string $bucketsPath, string $method)
    {
        parent::__construct($name);

        $this->setBucketsPath($bucketsPath);
        $this->setMethod($method);
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
}
