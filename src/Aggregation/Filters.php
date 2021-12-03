<?php

namespace Elastica\Aggregation;

use Elastica\Exception\InvalidException;
use Elastica\Query\AbstractQuery;

/**
 * Class Filters.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-filters-aggregation.html
 */
class Filters extends AbstractAggregation
{
    public const NAMED_TYPE = 1;
    public const ANONYMOUS_TYPE = 2;

    /**
     * @var int Type of bucket keys - named, or anonymous
     */
    private $_type;

    /**
     * Add a filter.
     *
     * If a name is given, it will be added as a key, otherwise considered as an anonymous filter
     *
     * @return $this
     */
    public function addFilter(AbstractQuery $filter, ?string $name = null): self
    {
        $type = null !== $name ? self::NAMED_TYPE : self::ANONYMOUS_TYPE;

        if ($this->hasParam('filters')
            && \count($this->getParam('filters'))
            && $this->_type !== $type
        ) {
            throw new InvalidException('Mix named and anonymous keys are not allowed');
        }

        $this->_type = $type;

        return $this->addParam('filters', $filter, $name);
    }

    /**
     * @return $this
     */
    public function setOtherBucket(bool $otherBucket): self
    {
        return $this->setParam('other_bucket', $otherBucket);
    }

    /**
     * @return $this
     */
    public function setOtherBucketKey(string $otherBucketKey): self
    {
        return $this->setParam('other_bucket_key', $otherBucketKey);
    }
}
