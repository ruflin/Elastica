<?php

namespace Elastica\Query;

\trigger_error('Elastica\Query\GeohashCell is deprecated.', E_USER_DEPRECATED);

/**
 * Class GeohashCell.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/2.4/query-dsl-geohash-cell-query.html
 */
class GeohashCell extends AbstractGeoDistance
{
    /**
     * @param string       $key       The field on which to query
     * @param array|string $location  Location as coordinates array or geohash string ['lat' => 40.3, 'lon' => 45.2]
     * @param string|int   $precision Integer length of geohash prefix or distance (3, or "50m")
     * @param bool         $neighbors if true, queries cells next to the given cell
     */
    public function __construct(string $key, $location, $precision = -1, bool $neighbors = false)
    {
        parent::__construct($key, $location);
        $this->setPrecision($precision);
        $this->setNeighbors($neighbors);
    }

    /**
     * Set the precision for this query.
     *
     * @param string|int $precision Integer length of geohash prefix or distance (3, or "50m")
     *
     * @return $this
     */
    public function setPrecision($precision): self
    {
        return $this->setParam('precision', $precision);
    }

    /**
     * Set the neighbours option for this query.
     *
     * @param bool $neighbours if true, queries cells next to the given cell
     *
     * @return $this
     */
    public function setNeighbors(bool $neighbours): self
    {
        return $this->setParam('neighbors', $neighbours);
    }
}
