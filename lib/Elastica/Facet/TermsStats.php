<?php

/**
 * Implements the statistical facet on a per term basis.
 *
 * @category Xodoa
 * @package Elastica
 * @author Tom Michaelis <tom.michaelis@gmail.com>
 * @link http://www.elasticsearch.org/guide/reference/api/search/facets/terms-stats-facet.html
 */
class Elastica_Facet_TermsStats extends Elastica_Facet_Abstract
{

    /**
     * Sets the key field for the query.
     *
     * @param  string                    $keyField The key field name for the query.
     * @return Elastica_Facet_TermsStats
     */
    public function setKeyField( $keyField )
    {
        return $this->setParam( 'key_field', $keyField );
    }

    /**
     * Sets a script to calculate statistical information on a per term basis
     *
     * @param  string                    $valueScript The script to do calculations on the statistical values
     * @return Elastica_Facet_TermsStats
     */
    public function setValueScript( $valueScript )
    {
        return $this->setParam( 'value_script', $valueScript );
    }

    /**
     * Sets a field to compute basic statistical results on
     *
     * @param  string                    $valueField The field to compute statistical values for
     * @return Elastica_Facet_TermsStats
     */
    public function setValueField( $valueField )
    {
        return $this->setParam( 'value_field', $valueField );
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
        $this->_setFacetParam( 'terms_stats', $this->_params );

        return parent::toArray();
    }

}
