<?php
namespace Elastica\Filter;

/**
 * Class GeohashCell.
 *
 * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-geohash-cell-filter.html
 */
class GeohashCell extends AbstractGeoDistance
{
    /**
     * @param string       $key       The field on which to filter
     * @param array|string $location  Location as coordinates array or geohash string ['lat' => 40.3, 'lon' => 45.2]
     * @param string|int   $precision Integer length of geohash prefix or distance (3, or "50m")
     * @param bool         $neighbors If true, filters cells next to the given cell.
     */
    public function __construct($key, $location, $precision = -1, $neighbors = false)
    {
        parent::__construct($key, $location);
        $this->setPrecision($precision);
        $this->setNeighbors($neighbors);
    }

    /**
     * Set the precision for this filter.
     *
     * @param string|int $precision Integer length of geohash prefix or distance (3, or "50m")
     *
     * @return $this
     */
    public function setPrecision($precision)
    {
        return $this->setParam('precision', $precision);
    }

    /**
     * Set the neighbors option for this filter.
     *
     * @param bool $neighbors If true, filters cells next to the given cell.
     *
     * @return $this
     */
    public function setNeighbors($neighbors)
    {
        return $this->setParam('neighbors', (bool) $neighbors);
    }
}
