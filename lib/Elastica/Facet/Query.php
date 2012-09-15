<?php
/**
 * Query facet
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @link http://www.elasticsearch.org/guide/reference/api/search/facets/query-facet.html
 */
class Elastica_Facet_Query extends Elastica_Facet_Abstract
{
    /**
     * Set the query for the facet.
     *
     * @param  Elastica_Query_Abstract $query
     * @return Elastica_Facet_Query
     */
    public function setQuery(Elastica_Query_Abstract $query)
    {
        return $this->_setFacetParam('query', $query->toArray());
    }
}
