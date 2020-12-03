<?php

namespace Elastica\Aggregation;

/**
 * Class GlobalAggregation.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-global-aggregation.html
 */
class GlobalAggregation extends AbstractAggregation
{
    public function toArray(): array
    {
        $array = parent::toArray();
        // Force json encoding to object
        $array[$this->_getBaseName()] = new \ArrayObject();

        return $array;
    }
}
