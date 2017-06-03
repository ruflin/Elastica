<?php
namespace Elastica\Aggregation;

use Elastica\Exception\InvalidException;
use Elastica\Query\AbstractQuery;

/**
 * Class Filters.
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-filters-aggregation.html
 */
class Filters extends AbstractAggregation
{
    const NAMED_TYPE = 1;
    const ANONYMOUS_TYPE = 2;

    /**
     * @var int Type of bucket keys - named, or anonymous
     */
    private $_type;

    /**
     * Add a filter.
     *
     * If a name is given, it will be added as a key, otherwise considered as an anonymous filter
     *
     * @param AbstractQuery $filter
     * @param string        $name
     *
     * @return $this
     */
    public function addFilter(AbstractQuery $filter, $name = null)
    {
        if (null !== $name && !is_string($name)) {
            throw new InvalidException('Name must be a string');
        }

        $filterArray = [];

        $type = self::NAMED_TYPE;

        if (null === $name) {
            $filterArray[] = $filter;
            $type = self::ANONYMOUS_TYPE;
        } else {
            $filterArray[$name] = $filter;
        }

        if ($this->hasParam('filters')
            && count($this->getParam('filters'))
            && $this->_type !== $type
        ) {
            throw new InvalidException('Mix named and anonymous keys are not allowed');
        }

        $this->_type = $type;

        return $this->addParam('filters', $filterArray);
    }

    /**
     * @param bool $otherBucket
     *
     * @return $this
     */
    public function setOtherBucket($otherBucket)
    {
        if (!is_bool($otherBucket)) {
            throw new \InvalidArgumentException('other_bucket only supports boolean values');
        }

        return $this->setParam('other_bucket', $otherBucket);
    }

    /**
     * @param string $otherBucketKey
     *
     * @return $this
     */
    public function setOtherBucketKey($otherBucketKey)
    {
        return $this->setParam('other_bucket_key', $otherBucketKey);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $array = [];
        $filters = $this->getParam('filters');

        foreach ($filters as $filter) {
            if (self::NAMED_TYPE === $this->_type) {
                $key = key($filter);
                $array['filters']['filters'][$key] = current($filter)->toArray();
            } else {
                $array['filters']['filters'][] = current($filter)->toArray();
            }
        }

        if ($this->hasParam('other_bucket')) {
            $array['filters']['other_bucket'] = $this->getParam('other_bucket');
        }

        if ($this->hasParam('other_bucket_key')) {
            $array['filters']['other_bucket_key'] = $this->getParam('other_bucket_key');
        }

        if ($this->_aggs) {
            $array['aggs'] = $this->_convertArrayable($this->_aggs);
        }

        return $array;
    }
}
