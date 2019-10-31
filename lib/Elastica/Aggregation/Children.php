<?php

namespace Elastica\Aggregation;

/**
 * Class Children.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-children-aggregation.html
 */
class Children extends AbstractAggregation
{
    /**
     * Set the type for this aggregation.
     *
     * @param string $type the child type the buckets in the parent space should be mapped to
     *
     * @return $this
     */
    public function setType(string $type): self
    {
        return $this->setParam('type', $type);
    }
}
