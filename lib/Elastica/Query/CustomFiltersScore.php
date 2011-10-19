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
	 * @param Elastica_Query_Abstract $query Query object
	 * @return Elastica_Query_CustomFiltersScore Current object
	 */
	public function setQuery(Elastica_Query_Abstract $query) {
		$this->setParam('query', $query->toArray());
		return $this;
	}

	/**
	 * Add a filter with boost
	 *
	 * @param Elastica_Filter_Abstract $filter Filter object
	 * @param float $boost Boost for the filter
	 * @return Elastica_Query_CustomFiltersScore Current object
	 */
	public function addFilter(Elastica_Filter_Abstract $filter, $boost) {
		$filter_param = array('filter' => $filter->toArray(), 'boost' => $boost);
		$this->addParam('filters', $filter_param);
		return $this;
	}
}
