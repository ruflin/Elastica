<?php
/**
 * Implements the statistical facet.
 *
 * @category Xodoa
 * @package Elastica
 * @author Robert Katzki <robert@katzki.de>
 * @link http://www.elasticsearch.org/guide/reference/api/search/facets/statistical-facet.html
 */
class Elastica_Facet_Statistical extends Elastica_Facet_Abstract
{
    /**
     * Sets the field for the statistical query.
     *
     * @param  string                     $field The field name for the statistical query.
     * @return Elastica_Facet_Statistical
     */
    public function setField($field)
    {
        return $this->setParam('field', $field);
    }

    /**
     * Sets multiple fields for the statistical query.
     *
     * @param  array                      $fields Numerical array with the fields for the statistical query.
     * @return Elastica_Facet_Statistical
     */
    public function setFields(array $fields)
    {
        return $this->setParam('fields', $fields);
    }

    /**
     * Sets a script to calculate statistical information
     *
     * @param  string                     $script The script to do calculations on the statistical values
     * @return Elastica_Facet_Statistical
     */
    public function setScript($script)
    {
        return $this->setParam('script', $script);
    }

    /**
     * Creates the full facet definition, which includes the basic
     * facet definition of the parent.
     *
     * @see Elastica_Facet_Abstract::toArray()
     * @return array
     */
    public function toArray()
    {
        $this->_setFacetParam('statistical', $this->_params);

        return parent::toArray();
    }
}
