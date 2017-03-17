<?php
namespace Elastica\Aggregation;

/**
 * A metric aggregation that computes the bounding box containing all geo_point values for a field.
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-metrics-geobounds-aggregation.html
 */
class GeoBounds extends AbstractAggregation
{
    /**
     * @param string $name  the name of this aggregation
     * @param string $field the field on which to perform this aggregation
     */
    public function __construct($name, $field)
    {
        parent::__construct($name);
        $this->setField($field);
    }

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
