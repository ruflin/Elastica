<?php

namespace Elastica\Filter;


/**
 * Class GeohashCell
 * @package Elastica
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/geohash-cell-filter/
 */
class GeohashCell extends AbstractGeoDistance
{
    /**
     * @param string $key The field on which to filter
     * @param array|string $location Location as coordinates array or geohash string ['lat' => 40.3, 'lon' => 45.2]
     * @param $precision Integer length of geohash prefix or distance (3, or "50m")
     * @param bool $neighbors If true, filters cells next to the given cell.
     */
    public function __construct($key, $location, $precision = -1, $neighbors = false)
    {
        parent::__construct($key, $location);
        $this->setPrecision($precision);
        $this->setNeighbors($neighbors);
    }

    /**
     * Set the precision for this filter
     * @param string|int $precision Integer length of geohash prefix or distance (3, or "50m")
     * @return \Elastica\Filter\GeohashCell
     */
    public function setPrecision($precision)
    {
        return $this->setParam('precision', $precision);
    }

    /**
     * Set the neighbors option for this filter
     * @param bool $neighbors If true, filters cells next to the given cell.
     * @return \Elastica\Filter\GeohashCell
     */
    public function setNeighbors($neighbors)
    {
        return $this->setParam('neighbors', (bool)$neighbors);
    }
}