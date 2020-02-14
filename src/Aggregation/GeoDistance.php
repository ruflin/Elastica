<?php

namespace Elastica\Aggregation;

use Elastica\Exception\InvalidException;

/**
 * Class GeoDistance.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-geodistance-aggregation.html
 */
class GeoDistance extends AbstractAggregation
{
    public const DISTANCE_TYPE_ARC = 'arc';
    public const DISTANCE_TYPE_PLANE = 'plane';

    public const DEFAULT_DISTANCE_TYPE_VALUE = self::DISTANCE_TYPE_ARC;
    public const DEFAULT_UNIT_VALUE = 'm';

    /**
     * @param string       $name   the name if this aggregation
     * @param string       $field  the field on which to perform this aggregation
     * @param array|string $origin the point from which distances will be calculated
     */
    public function __construct(string $name, string $field, $origin)
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
    public function setField(string $field): self
    {
        return $this->setParam('field', $field);
    }

    /**
     * Set the origin point from which distances will be calculated.
     *
     * @param array|string $origin valid formats are array("lat" => 52.3760, "lon" => 4.894), "52.3760, 4.894", and array(4.894, 52.3760)
     *
     * @return $this
     */
    public function setOrigin($origin): self
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
    public function addRange(?int $fromValue = null, ?int $toValue = null): self
    {
        if (null === $fromValue && null === $toValue) {
            throw new InvalidException('Either fromValue or toValue must be set. Both cannot be null.');
        }

        $range = [];

        if (null !== $fromValue) {
            $range['from'] = $fromValue;
        }

        if (null !== $toValue) {
            $range['to'] = $toValue;
        }

        return $this->addParam('ranges', $range);
    }

    /**
     * Set the unit of distance measure for this aggregation.
     *
     * @param string $unit defaults to m
     *
     * @return $this
     */
    public function setUnit(string $unit): self
    {
        return $this->setParam('unit', $unit);
    }

    /**
     * Set the method by which distances will be calculated.
     *
     * @param string $distanceType see DISTANCE_TYPE_* constants for options. Defaults to arc.
     *
     * @return $this
     */
    public function setDistanceType(string $distanceType): self
    {
        return $this->setParam('distance_type', $distanceType);
    }
}
