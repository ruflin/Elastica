<?php

namespace Elastica\Filter;

use Elastica\Filter\AbstractFilter;

/**
 * geo_shape filter for pre-indexed shapes
 *
 * Filter pre-indexed shape definitions
 *
 * @category Xodoa
 * @package Elastica
 * @author Christian Hansen <quid@gmx.de>
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/geo-shape-filter/
 */
class GeoShapePreIndexed extends AbstractFilter
{
    /**
     * Key
     *
     * @var string Key
     */
    protected $_key = '';

	/**
     * elasticsearch id of the pre-indexed shape
     *
     * @var string id
     */
    protected $_id = '';

	/**
     * elasticsearch type of the pre-indexed shape
     *
     * @var string type
     */
    protected $_type = '';

	/**
     *  elasticsearch index of the pre-indexed shape
     *
     * @var string index
     */
    protected $_index = '';

	/**
     *  elasticsearch field name of the pre-indexed shape
     *
     * @var string index
     */
    protected $_shape_field_name = '';

    /**
     * Construct geo_shape filter with a pre-indexed shape
     *
     * @param string $key				Key
     * @param string $id				Id of the pre-indexed shape
     * @param string $type				Id of the pre-indexed shape
     * @param string $index				Id of the pre-indexed shape
     * @param string $shape_field_name	shape_field_name of the pre-indexed shape
     */
    public function __construct($key, $id, $type, $index, $shape_field_name)
    {
        $this->_key = $key;
        $this->_id = $id;
        $this->_type = $type;
        $this->_index = $index;
        $this->_shape_field_name = $shape_field_name;
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
                $this->_key => array(
					'indexed_shape' => array(
						'id' => $this->_id,
						'type' => $this->_type,
						'index' => $this->_index,
						'shape_field_name' => $this->_shape_field_name
					)
                )
            )
        );
    }
}
