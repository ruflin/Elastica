<?php
/**
 * Filter facet
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @link http://www.elasticsearch.org/guide/reference/api/search/facets/filter-facet.html
 */
class Elastica_Facet_Filter extends Elastica_Facet_Abstract
{
    /**
     * Set the filter for the facet.
     *
     * @param  Elastica_Filter_Abstract $filter
     * @return Elastica_Facet_Filter
     */
    public function setFilter(Elastica_Filter_Abstract $filter)
    {
        return $this->_setFacetParam('filter', $filter->toArray());
    }
}
