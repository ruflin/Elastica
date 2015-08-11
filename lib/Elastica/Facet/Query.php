<?php
namespace Elastica\Facet;

use Elastica\Query\AbstractQuery;

/**
 * Query facet.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 *
 * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/search-facets-query-facet.html
 * @deprecated Facets are deprecated and will be removed in a future release. You are encouraged to migrate to aggregations instead.
 */
class Query extends AbstractFacet
{
    /**
     * Set the query for the facet.
     *
     * @param \Elastica\Query\AbstractQuery $query
     *
     * @return $this
     */
    public function setQuery(AbstractQuery $query)
    {
        return $this->_setFacetParam('query', $query);
    }
}
