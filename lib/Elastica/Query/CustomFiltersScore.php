<?php
/**
 * Custom filtered score query. Needs a query and array of filters, with boosts
 *
 * @uses Elastica_Query_Abstract
 * @category Xodoa
 * @package Elastica
 * @author James Wilson <jwilson556@gmail.com>
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/custom-filters-score-query.html
 */
class Elastica_Query_CustomFiltersScore extends Elastica_Query_Abstract
{
    /**
     * Sets a query
     *
     * @param  Elastica_Query_Abstract           $query Query object
     * @return Elastica_Query_CustomFiltersScore Current object
     */
    public function setQuery(Elastica_Query_Abstract $query)
    {
        $this->setParam('query', $query->toArray());

        return $this;
    }

    /**
     * Add a filter with boost
     *
     * @param  Elastica_Filter_Abstract          $filter Filter object
     * @param  float                             $boost  Boost for the filter
     * @return Elastica_Query_CustomFiltersScore Current object
     */
    public function addFilter(Elastica_Filter_Abstract $filter, $boost)
    {
        $filterParam = array('filter' => $filter->toArray(), 'boost' => $boost);
        $this->addParam('filters', $filterParam);

        return $this;
    }

    /**
     * Add a filter with a script to calculate the score
     *
     * @param  Elastica_Filter_Abstract          $filter Filter object
     * @param  Elastica_Script                   $script Script for calculating the score
     * @return Elastica_Query_CustomFiltersScore Current object
     */
    public function addFilterScript(Elastica_Filter_Abstract $filter, Elastica_Script $script)
    {
        $filterParam = array('filter' => $filter->toArray(), 'script' => $script->getScript());
        $this->addParam('filters', $filterParam);

        return $this;
    }
}
