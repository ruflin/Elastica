<?php

/**
 * Constant score query
 *
 * @uses Elastica_Query_Abstract
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/constant-score-query.html
 */
class Elastica_Query_ConstantScore extends Elastica_Query_Abstract
{

	/**
	 * @param null|Elastica_Filter_Abstract|array $filter
	 */
	public function __construct($filter = null) {
		if(!is_null($filter)) {
			$this->setFilter($filter);
		}
	}

	/**
	 * @param array|Elastica_Filter_Abstract $filter
	 * @return Elastica_Query_ConstantScore Query object
	 */
	public function setFilter($filter) {
		if ($filter instanceof Elastica_Filter_Abstract) {
			$filter = $filter->toArray();
		}
		return $this->setParam('filter', $filter);
	}

	/**
	 * @param float $boost
	 * @return Elastica_Query_ConstantScore
	 */
	public function setBoost($boost) {
		return $this->setParam('boost', $boost);
	}
}

