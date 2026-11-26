<?php

declare(strict_types=1);

namespace Elastica\Query;

/**
 * geo_shape query for pre-indexed shapes.
 *
 * Query pre-indexed shape definitions
 *
 * @author Bennie Krijger <benniekrijger@gmail.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-geo-shape-query.html
 */
class GeoShapePreIndexed extends AbstractGeoShape
{
    /**
     * elasticsearch id of the pre-indexed shape.
     */
    protected string $_indexedId;

    /**
     *  elasticsearch index of the pre-indexed shape.
     */
    protected string $_indexedIndex;

    /**
     *  elasticsearch path/field name of the pre-indexed shape.
     */
    protected string $_indexedPath;

    protected ?string $_indexedRouting;

    /**
     * Construct geo_shape query with a pre-indexed shape.
     *
     * @param string      $path           The path/field of the shape searched
     * @param string      $indexedId      Id of the pre-indexed shape
     * @param string      $indexedIndex   Index of the pre-indexed shape
     * @param string      $indexedPath    Path of the pre-indexed shape
     * @param string|null $indexedRouting Routing of the pre-indexed shape
     */
    public function __construct(
        string $path,
        string $indexedId,
        string $indexedIndex,
        string $indexedPath,
        ?string $indexedRouting = null,
    ) {
        $this->_path = $path;
        $this->_indexedId = $indexedId;
        $this->_indexedIndex = $indexedIndex;
        $this->_indexedPath = $indexedPath;
        $this->_indexedRouting = $indexedRouting;
    }

    public function toArray(): array
    {
        $indexedShape = [
            'id' => $this->_indexedId,
            'index' => $this->_indexedIndex,
            'path' => $this->_indexedPath,
        ];
        if (null !== $this->_indexedRouting) {
            $indexedShape['routing'] = $this->_indexedRouting;
        }

        return [
            'geo_shape' => [
                $this->_path => [
                    'indexed_shape' => $indexedShape,
                    'relation' => $this->_relation,
                ],
            ],
        ];
    }
}
