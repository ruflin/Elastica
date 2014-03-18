<?php

namespace Elastica\Filter;

/**
 * geo_shape filter for pre-indexed shapes
 *
 * Filter pre-indexed shape definitions
 *
 * @category Xodoa
 * @package Elastica
 * @author Bennie Krijger <benniekrijger@gmail.com>
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/geo-shape-filter/
 */
class GeoShapePreIndexed extends AbstractGeoShape
{
    /**
     * elasticsearch id of the pre-indexed shape
     *
     * @var string
     */
    protected $_indexedId;

    /**
     * elasticsearch type of the pre-indexed shape
     *
     * @var string
     */
    protected $_indexedType;

    /**
     *  elasticsearch index of the pre-indexed shape
     *
     * @var string
     */
    protected $_indexedIndex;

    /**
     *  elasticsearch path/field name of the pre-indexed shape
     *
     * @var string
     */
    protected $_indexedPath;

    /**
     * Construct geo_shape filter with a pre-indexed shape
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
     * Converts filter to array
     *
     * @see \Elastica\Filter\AbstractFilter::toArray()
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
                        'path' => $this->_indexedPath
                    ),
                    'relation' => $this->_relation
                )
            )
        );
    }
}
