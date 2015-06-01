<?php
namespace Elastica\Facet;

/**
 * Implements the Date Histogram facet.
 *
 * @author Raul Martinez Jr  <juneym@gmail.com>
 *
 * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/search-facets-date-histogram-facet.html
 * @link https://github.com/elasticsearch/elasticsearch/issues/591
 * @deprecated Facets are deprecated and will be removed in a future release. You are encouraged to migrate to aggregations instead.
 */
class DateHistogram extends Histogram
{
    /**
     * Set the time_zone parameter.
     *
     * @param string $tzOffset
     *
     * @return $this
     */
    public function setTimezone($tzOffset)
    {
        return $this->setParam('time_zone', $tzOffset);
    }

    /**
     * Set the factor parameter.
     *
     * @param int $factor
     *
     * @return $this
     */
    public function setFactor($factor)
    {
        return $this->setParam('factor', $factor);
    }

    /**
     * Creates the full facet definition, which includes the basic
     * facet definition of the parent.
     *
     * @see \Elastica\Facet\AbstractFacet::toArray()
     *
     * @throws \Elastica\Exception\InvalidException When the right fields haven't been set.
     *
     * @return array
     */
    public function toArray()
    {
        /*
         * Set the range in the abstract as param.
         */
        $this->_setFacetParam('date_histogram', $this->_params);

        return $this->_facet;
    }
}
