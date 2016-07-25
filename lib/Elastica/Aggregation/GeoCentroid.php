<?php

namespace Elastica\Aggregation;

use Elastica\Exception\InvalidException;

/**
 * Class GeoCentroid.
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-metrics-geocentroid-aggregation.html
 */
class GeoCentroid extends AbstractAggregation
{
    /**
     * Set the field for this aggregation.
     *
     * @param string $field the name of the document field on which to perform this aggregation
     *
     * @return $this
     */
    public function setField($field)
    {
        return $this->setParam('field', $field);
    }
}
