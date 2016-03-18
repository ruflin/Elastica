<?php

namespace Elastica\Query;

/**
 * geo_shape query for pre-indexed shapes.
 *
 * Query pre-indexed shape definitions
 *
 * @author Bennie Krijger <benniekrijger@gmail.com>
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-geo-shape-query.html
 */
class GeoShapePreIndexed extends AbstractGeoShape
{
    /**
     * elasticsearch id of the pre-indexed shape.
     *
     * @var string
     */
    protected $_indexedId;

    /**
     * elasticsearch type of the pre-indexed shape.
     *
     * @var string
     */
    protected $_indexedType;

    /**
     *  elasticsearch index of the pre-indexed shape.
     *
     * @var string
     */
    protected $_indexedIndex;

    /**
     *  elasticsearch path/field name of the pre-indexed shape.
     *
     * @var string
     */
    protected $_indexedPath;

    /**
     * Construct geo_shape query with a pre-indexed shape.
     *
     * @param string $path         The path/field of the shape searched
     * @param string $indexedId    Id of the pre-indexed shape
     * @param string $indexedType  Type of the pre-indexed shape
     * @param string $indexedIndex Index of the pre-indexed shape
     * @param string $indexedPath  Path of the pre-indexed shape
     */
    public function __construct($path, $indexedId, $indexedType, $indexedIndex, $indexedPath)
    {
        $this->_path = $path;
        $this->_indexedId = $indexedId;
        $this->_indexedType = $indexedType;
        $this->_indexedIndex = $indexedIndex;
        $this->_indexedPath = $indexedPath;
    }

    /**
     * Converts query to array.
     *
     * @see \Elastica\Query\AbstractQuery::toArray()
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            'geo_shape' => array(
                $this->_path => array(
                    'indexed_shape' => array(
                        'id' => $this->_indexedId,
                        'type' => $this->_indexedType,
                        'index' => $this->_indexedIndex,
                        'path' => $this->_indexedPath,
                    ),
                    'relation' => $this->_relation,
                ),
            ),
        );
    }
}
