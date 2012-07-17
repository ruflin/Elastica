<?php
/**
 * Range Filter
 *
 * @uses Elastica_Filter_Abstract
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/range-filter.html
 */
class Elastica_Filter_Range extends Elastica_Filter_Abstract
{
    /**
     * Fields
     *
     * @var array Fields
     */
    protected $_fields = array();

    /**
     * Construct range filter
     *
     * @param  string                $fieldName Field name
     * @param  array                 $args      Field arguments
     * @return Elastica_Filter_Range
     */
    public function __construct($fieldName = false, array $args = array())
    {
        if ($fieldName) {
            $this->addField($fieldName, $args);
        }
    }

    /**
     * Ads a field with arguments to the range query
     *
     * @param  string                $fieldName Field name
     * @param  array                 $args      Field arguments
     * @return Elastica_Filter_Range
     */
    public function addField($fieldName, array $args)
    {
        $this->_fields[$fieldName] = $args;

        return $this;
    }

    /**
     * Convers object to array
     *
     * @see Elastica_Filter_Abstract::toArray()
     * @return array Filter array
     */
    public function toArray()
    {
        $this->setParams($this->_fields);

        return parent::toArray();
    }
}
