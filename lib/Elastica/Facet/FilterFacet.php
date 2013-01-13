<?php

namespace Elastica\Facet;
use Elastica\Filter\AbstractFilter;

/**
 * Filter facet
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @link http://www.elasticsearch.org/guide/reference/api/search/facets/filter-facet.html
 */
class FilterFacet extends AbstractFacet
{
    /**
     * Set the filter for the facet.
     *
     * @param  Elastica\Filter\AbstractFilter $filter
     * @return Elastica\Facet\FilterFacet
     */
    public function setFilter(AbstractFilter $filter)
    {
        return $this->_setFacetParam('filter', $filter->toArray());
    }
}
