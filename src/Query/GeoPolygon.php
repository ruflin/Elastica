<?php

namespace Elastica\Query;

/**
 * Geo polygon query.
 *
 * @author Michael Maclean <mgdm@php.net>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-geo-polygon-query.html
 */
class GeoPolygon extends AbstractQuery
{
    /**
     * Key.
     *
     * @var string Key
     */
    protected $_key;

    /**
     * Points making up polygon.
     *
     * @var array Points making up polygon
     */
    protected $_points;

    /**
     * Construct polygon query.
     *
     * @param string $key    Key
     * @param array  $points Points making up polygon
     */
    public function __construct(string $key, array $points)
    {
        $this->_key = $key;
        $this->_points = $points;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return [
            'geo_polygon' => [
                $this->_key => [
                    'points' => $this->_points,
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        return \count($this->_points);
    }
}
