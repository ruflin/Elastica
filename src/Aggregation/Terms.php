<?php

namespace Elastica\Aggregation;

/**
 * Class Terms.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-terms-aggregation.html
 */
class Terms extends AbstractTermsAggregation
{
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
     * Sets a list of bucket sort orders.
     *
     * @param array $orders a list of [<aggregationField>|"_count"|"_term" => <direction>] definitions
     *
     * @return $this
     */
    public function setOrders(array $orders): self
    {
        return $this->setParam('order', $orders);
    }
}
