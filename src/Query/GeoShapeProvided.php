<?php

declare(strict_types=1);

namespace Elastica\Query;

/**
 * geo_shape query for provided shapes.
 *
 * Query provided shape definitions
 *
 * @author BennieKrijger <benniekrijger@gmail.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-geo-shape-query.html
 */
class GeoShapeProvided extends AbstractGeoShape
{
    public const TYPE_ENVELOPE = 'envelope';
    public const TYPE_MULTIPOINT = 'multipoint';
    public const TYPE_POINT = 'point';
    public const TYPE_MULTIPOLYGON = 'multipolygon';
    public const TYPE_LINESTRING = 'linestring';
    public const TYPE_POLYGON = 'polygon';

    /**
     * Type of the geo_shape.
     */
    protected string $_shapeType;

    /**
     * Coordinates making up geo_shape.
     *
     * @var array Coordinates making up geo_shape
     */
    protected array $_coordinates;

    /**
     * Construct geo_shape query.
     *
     * @param string $path        The path/field of the shape searched
     * @param array  $coordinates Points making up the shape
     * @param string $shapeType   Type of the geo_shape:
     *                            point, envelope, linestring, polygon,
     *                            multipoint or multipolygon
     */
    public function __construct(string $path, array $coordinates, string $shapeType = self::TYPE_ENVELOPE)
    {
        $this->_path = $path;
        $this->_shapeType = $shapeType;
        $this->_coordinates = $coordinates;
    }

    public function toArray(): array
    {
        return [
            'geo_shape' => [
                $this->_path => [
                    'shape' => [
                        'type' => $this->_shapeType,
                        'coordinates' => $this->_coordinates,
                    ],
                    'relation' => $this->_relation,
                ],
            ],
        ];
    }
}
