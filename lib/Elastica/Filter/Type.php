<?php
/**
 * Type Filter
 *
 * @uses Elastica_Filter_Abstract
 * @category Xodoa
 * @package Elastica
 * @author James Wilson <jwilson556@gmail.com>
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/type-filter.html
 */
class Elastica_Filter_Type extends Elastica_Filter_Abstract
{
    /**
     * Type
     *
     * @var Elastica_Type Type object
     */
    protected $_type = null;

    /**
     * Construct Type Filter
     *
     * @param  string               $typeName Type name
     * @return Elastica_Filter_Type
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
     * @param  string               $typeName Type name
     * @return Elastica_Filter_Type current object
     */
    public function setType($typeName)
    {
        $this->_type = $typeName;

        return $this;
    }

    /**
     * Convert object to array
     *
     * @see Elastica_Filter_Abstract::toArray()
     * @return array Filter array
     */
    public function toArray()
    {
        return array(
            'type' => array('value' => $this->_type)
        );
    }
}
