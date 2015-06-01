<?php
namespace Elastica\Facet;

use Elastica\Filter\AbstractFilter;

/**
 * Filter facet.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 *
 * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/search-facets-filter-facet.html
 * @deprecated Facets are deprecated and will be removed in a future release. You are encouraged to migrate to aggregations instead.
 */
class Filter extends AbstractFacet
{
    /**
     * Set the filter for the facet.
     *
     * @param \Elastica\Filter\AbstractFilter $filter
     *
     * @return $this
     */
    public function setFilter(AbstractFilter $filter)
    {
        return $this->_setFacetParam('filter', $filter->toArray());
    }
}
