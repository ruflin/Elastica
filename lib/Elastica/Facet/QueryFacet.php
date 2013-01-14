<?php

namespace Elastica\Facet;
use Elastica\Query\AbstractQuery;

/**
 * Query facet
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @link http://www.elasticsearch.org/guide/reference/api/search/facets/query-facet.html
 */
class QueryFacet extends AbstractFacet
{
    /**
     * Set the query for the facet.
     *
     * @param  \Elastica\Query\AbstractQuery $query
     * @return \Elastica\Facet\QueryFacet
     */
    public function setQuery(AbstractQuery $query)
    {
        return $this->_setFacetParam('query', $query->toArray());
    }
}
