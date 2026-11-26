<?php

declare(strict_types=1);

namespace Elastica\Aggregation;

/**
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-multi-terms-aggregation.html
 */
class MultiTerms extends AbstractAggregation
{
    use Traits\ShardSizeTrait;

    /**
     * Each entry describes a field or script, with optional missing/value_type settings.
     *
     * @param array[] $terms
     *
     * @return $this
     */
    public function setTerms(array $terms): self
    {
        return $this->setParam('terms', $terms);
    }

    /**
     * @return $this
     */
    public function addTerm(array $term): self
    {
        return $this->addParam('terms', $term);
    }

    /**
     * @return $this
     */
    public function setSize(int $size): self
    {
        return $this->setParam('size', $size);
    }

    /**
     * @return $this
     */
    public function setOrder(string $order, string $direction = 'asc'): self
    {
        return $this->setParam('order', [$order => $direction]);
    }

    /**
     * @return $this
     */
    public function setOrders(array $orders): self
    {
        return $this->setParam('order', $orders);
    }

    /**
     * @return $this
     */
    public function setMinimumDocumentCount(int $count): self
    {
        return $this->setParam('min_doc_count', $count);
    }

    /**
     * @return $this
     */
    public function setShardMinimumDocumentCount(int $count): self
    {
        return $this->setParam('shard_min_doc_count', $count);
    }

    /**
     * @param string $mode breadth_first or depth_first
     *
     * @return $this
     */
    public function setCollectMode(string $mode): self
    {
        return $this->setParam('collect_mode', $mode);
    }

    /**
     * @return $this
     */
    public function setShowTermDocCountError(bool $show): self
    {
        return $this->setParam('show_term_doc_count_error', $show);
    }
}
