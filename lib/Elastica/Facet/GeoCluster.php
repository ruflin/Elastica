<?php

namespace Elastica\Facet;

/**
 * Implements the Geo Cluster facet.
 *
 * @category Xodoa
 * @package Elastica
 * @author Konstantin Nikiforov <konstantin.nikiforov@gmail.com>
 * @link http://www.elasticsearch.org/guide/reference/api/search/facets/geo-cluster-facet.html
 */
class GeoCluster extends AbstractFacet {

    /**
     * @param $fieldName
     * @return $this
     */
    public function setField($fieldName) {
        $this->setParam('field', $fieldName);
        return $this;
    }

    /**
     * @param float $factor
     * @return $this
     */
    public function setFactor(float $factor){
        $this->setParam('factor', $factor);
        return $this;
    }

    /**
     * Creates the full facet definition, which includes the basic
     * facet definition of the parent.
     *
     * @see \Elastica\Facet\AbstractFacet::toArray()
     * @throws \Elastica\Exception\InvalidException When the right fields haven't been set.
     * @return array
     */
    public function toArray(){
        /**
         * Set the geo_cluster in the abstract as param.
         */
        $this->_setFacetParam ('geo_cluster', $this->_params);

        return parent::toArray();
    }
}
