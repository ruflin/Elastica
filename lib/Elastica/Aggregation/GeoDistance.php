<?php
namespace Elastica\Aggregation;

use Elastica\Exception\InvalidException;

/**
 * Class GeoDistance.
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-geodistance-aggregation.html
 */
class GeoDistance extends AbstractAggregation
{
    const DISTANCE_TYPE_SLOPPY_ARC = 'sloppy_arc';
    const DISTANCE_TYPE_ARC = 'arc';
    const DISTANCE_TYPE_PLANE = 'plane';

    /**
     * @param string       $name   the name if this aggregation
     * @param string       $field  the field on which to perform this aggregation
     * @param string|array $origin the point from which distances will be calculated
     */
    public function __construct($name, $field, $origin)
    {
        parent::__construct($name);
        $this->setField($field)->setOrigin($origin);
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

    /**
     * Set the origin point from which distances will be calculated.
     *
     * @param string|array $origin valid formats are array("lat" => 52.3760, "lon" => 4.894), "52.3760, 4.894", and array(4.894, 52.3760)
     *
     * @return $this
     */
    public function setOrigin($origin)
    {
        return $this->setParam('origin', $origin);
    }

    /**
     * Add a distance range to this aggregation.
     *
     * @param int $fromValue a distance
     * @param int $toValue   a distance
     *
     * @throws \Elastica\Exception\InvalidException
     *
     * @return $this
     */
    public function addRange($fromValue = null, $toValue = null)
    {
        if (is_null($fromValue) && is_null($toValue)) {
            throw new InvalidException('Either fromValue or toValue must be set. Both cannot be null.');
        }

        $range = [];

        if (!is_null($fromValue)) {
            $range['from'] = $fromValue;
        }

        if (!is_null($toValue)) {
            $range['to'] = $toValue;
        }

        return $this->addParam('ranges', $range);
    }

    /**
     * Set the unit of distance measure for  this aggregation.
     *
     * @param string $unit defaults to km
     *
     * @return $this
     */
    public function setUnit($unit)
    {
        return $this->setParam('unit', $unit);
    }

    /**
     * Set the method by which distances will be calculated.
     *
     * @param string $distanceType see DISTANCE_TYPE_* constants for options. Defaults to sloppy_arc.
     *
     * @return $this
     */
    public function setDistanceType($distanceType)
    {
        return $this->setParam('distance_type', $distanceType);
    }
}
