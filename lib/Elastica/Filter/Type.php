<?php

namespace Elastica\Filter;

/**
 * Type Filter
 *
 * @category Xodoa
 * @package Elastica
 * @author James Wilson <jwilson556@gmail.com>
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/type-filter.html
 */
class Type extends AbstractFilter
{
    /**
     * Type name
     *
     * @var string
     */
    protected $_type = null;

    /**
     * Construct Type Filter
     *
     * @param  string                $typeName Type name
     * @return \Elastica\Filter\Type
     */
    public function __construct($typeName = null)
    {
        if ($typeName) {
            $this->setType($typeName);
        }
    }

    /**
     * Ads a field with arguments to the range query
     *
     * @param  string                $typeName Type name
     * @return \Elastica\Filter\Type current object
     */
    public function setType($typeName)
    {
        $this->_type = $typeName;

        return $this;
    }

    /**
     * Convert object to array
     *
     * @see \Elastica\Filter\AbstractFilter::toArray()
     * @return array Filter array
     */
    public function toArray()
    {
        return array(
            'type' => array('value' => $this->_type),
        );
    }
}
