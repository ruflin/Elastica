<?php

namespace Elastica\Aggregation;

/**
 * Class Histogram.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-histogram-aggregation.html
 */
class Histogram extends AbstractSimpleAggregation
{
    use Traits\KeyedTrait;
    use Traits\MissingTrait;

    /**
     * @param string     $name     the name of this aggregation
     * @param string     $field    the name of the field on which to perform the aggregation
     * @param int|string $interval the interval by which documents will be bucketed
     */
    public function __construct(string $name, string $field, $interval)
    {
        parent::__construct($name);
        $this->setField($field);
        $this->setInterval($interval);
    }

    /**
     * Set the interval by which documents will be bucketed.
     *
     * @param int|string $interval
     *
     * @return $this
     */
    public function setInterval($interval): self
    {
        return $this->setParam('interval', $interval);
    }

    /**
     * Set the bucket sort order.
     *
     * @param string $order     "_count", "_term", or the name of a sub-aggregation or sub-aggregation response field
     * @param string $direction "asc" or "desc"
     *
     * @return $this
     */
    public function setOrder(string $order, string $direction): self
    {
        return $this->setParam('order', [$order => $direction]);
    }

    /**
     * Set the minimum number of documents which must fall into a bucket in order for the bucket to be returned.
     *
     * @param int $count set to 0 to include empty buckets
     *
     * @return $this
     */
    public function setMinimumDocumentCount(int $count): self
    {
        return $this->setParam('min_doc_count', $count);
    }
}
