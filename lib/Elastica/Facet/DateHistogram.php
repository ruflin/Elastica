<?php
/**
 * Implements the Date Histogram facet.
 *
 * @category Xodoa
 * @package Elastica
 * @author Raul Martinez Jr  <juneym@gmail.com>
 * @link http://www.elasticsearch.org/guide/reference/api/search/facets/date-histogram-facet.html
 * @link https://github.com/elasticsearch/elasticsearch/issues/591
 */
class Elastica_Facet_DateHistogram extends Elastica_Facet_Histogram
{
    /**
     * Set the time_zone parameter
     *
     * @param  string $tzOffset
     * @return void
     */
    public function setTimezone($tzOffset)
    {
        return $this->setParam('time_zone', $tzOffset);
    }

    /**
     * Creates the full facet definition, which includes the basic
     * facet definition of the parent.
     *
     * @see Elastica_Facet_Abstract::toArray()
     * @throws Elastica_Exception_Invalid When the right fields haven't been set.
     * @return array
     */
    public function toArray()
    {
        /**
         * Set the range in the abstract as param.
         */
        $this->_setFacetParam('date_histogram', $this->_params);

        return $this->_facet;
    }
}
