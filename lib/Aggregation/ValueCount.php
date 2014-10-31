<?php

namespace Elastica\Aggregation;

/**
 * Class ValueCount
 * @package Elastica\Aggregation
 * @link http://www.elasticsearch.org/guide/en/elasticsearch/reference/master/search-aggregations-metrics-valuecount-aggregation.html
 */
class ValueCount extends AbstractAggregation
{
    /**
     * @param string $name the name of this aggregation
     * @param string $field the field on which to perform this aggregation
     */
    public function __construct($name, $field)
    {
        parent::__construct($name);
        $this->setField($field);
    }

    /**
     * Set the field for this aggregation
     * @param string $field the name of the document field on which to perform this aggregation
     * @return ValueCount
     */
    public function setField($field)
    {
        return $this->setParam('field', $field);
    }
} 