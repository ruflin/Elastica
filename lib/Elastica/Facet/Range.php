<?php
/**
 * Implements the range facet.
 *
 * @category Xodoa
 * @package Elastica
 * @author Jasper van Wanrooy <jasper@vanwanrooy.net>
 * @link http://www.elasticsearch.org/guide/reference/api/search/facets/range-facet.html
 */
class Elastica_Facet_Range extends Elastica_Facet_Abstract
{
    /**
     * Sets the field for the range.
     *
     * @param  string               $field The name of the field for range.
     * @return Elastica_Facet_Range
     */
    public function setField($field)
    {
        return $this->setParam('field', $field);
    }

    /**
     * Sets the fields by their separate key and value fields.
     *
     * @param  string               $keyField   The key_field param for the range.
     * @param  string               $valueField The key_value param for the range.
     * @return Elastica_Facet_Range
     */
    public function setKeyValueFields($keyField, $valueField)
    {
        return $this->setParam('key_field', $keyField)
                    ->setParam('value_field', $valueField);
    }

    /**
     * Sets the key and value for this facet by script.
     *
     * @param string $keyScript   Script to check whether it falls into the range.
     * @param string $valueScript Script to use for statistical calculations.
     */
    public function setKeyValueScripts($keyScript, $valueScript)
    {
        return $this->setParam('key_script', $keyScript)
                    ->setParam('value_script', $valueScript);
    }

    /**
     * Sets the ranges for the facet all at once. Sample ranges:
     * array (
     *     array('to' => 50),
     *     array('from' => 20, 'to' 70),
     *     array('from' => 70, 'to' => 120),
     *     array('from' => 150)
     * )
     *
     * @param  array                $ranges Numerical array with range definitions.
     * @return Elastica_Facet_Range
     */
    public function setRanges(array $ranges)
    {
        return $this->setParam('ranges', $ranges);
    }

    /**
     * Adds a range to the range facet.
     *
     * @param  mixed                $from The from for the range.
     * @param  mixed                $to   The to for the range.
     * @return Elastica_Facet_Range
     */
    public function addRange($from = null, $to = null)
    {
        if (!isset($this->_params['ranges']) || !is_array($this->_params['ranges'])) {
            $this->_params['ranges'] = array();
        }

        $this->_params['ranges'][] = array('from' => $from, 'to' => $to);

        return $this;
    }

    /**
     * Creates the full facet definition, which includes the basic
     * facet definition of the parent.
     *
     * @see Elastica_Facet_Abstract::toArray()
     * @throws Elastica_Exception_Invalid When the right fields haven't been set.
     * @return array
     */
    public function toArray()
    {
        /**
         * Check the facet for validity.
         * There are three ways to set the key and value field for the range:
         *  - a single field for both key and value; or
         *  - separate fields for key and value; or
         *  - separate scripts for key and value.
         */
        $fieldTypesSet = 0;
        if (isset($this->_params['field'])) {
            $fieldTypesSet++;
        }
        if (isset($this->_params['key_field'])) {
            $fieldTypesSet++;
        }
        if (isset($this->_params['key_script'])) {
            $fieldTypesSet++;
        }

        if ($fieldTypesSet === 0) {
            throw new Elastica_Exception_Invalid('Neither field, key_field nor key_script is set.');
        } elseif ($fieldTypesSet > 1) {
            throw new Elastica_Exception_Invalid('Either field, key_field and key_value or key_script and value_script should be set.');
        }

        /**
         * Set the range in the abstract as param.
         */
        $this->_setFacetParam('range', $this->_params);

        return parent::toArray();
    }
}
