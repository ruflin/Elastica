<?php

namespace Elastica\Facet;

/**
 * Implements the Geo Cluster facet.
 *
 * @category Xodoa
 * @package Elastica
 * @author Konstantin Nikiforov <konstantin.nikiforov@gmail.com>
 * @link https://github.com/zenobase/geocluster-facet
 */
class GeoCluster extends AbstractFacet {

    /**
     * @param string $fieldName
     * @return $this
     */
    public function setField($fieldName) {
        $this->setParam('field', $fieldName);
        return $this;
    }

    /**
     * @param double $factor
     * @return $this
     */
    public function setFactor($factor){
        $this->setParam('factor', $factor);
        return $this;
    }

    /**
     * @param boolean $showIds
     * @return $this
     */
    public function setShowIds($showIds) {
        $this->setParam('showIds', $showIds);
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
        $this->_setFacetParam ('geo_cluster', $this->_params);
        return parent::toArray();
    }
}
