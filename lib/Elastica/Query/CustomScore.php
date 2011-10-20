<?php

/**
 * Custom score query
 *
 * @uses Elastica_Query_Abstract
 * @category Xodoa
 * @package Elastica
 * @author Wu Yang <darkyoung@gmail.com>
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/custom-score-query.html
 */
class Elastica_Query_CustomScore extends Elastica_Query_Abstract
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
	 * Sets query object
	 *
	 * @param string|Elastica_Query|Elastica_Query_Abstract $query
	 * @return Elastica_Query_CustomScore
	 */
	public function setQuery($query) {
		$query = Elastica_Query::create($query);
		$data = $query->toArray();
		return $this->setParam('query', $data['query']);
	}

	/**
	 * @param string $script
	 * @return Elastica_Query_CustomScore
	 */
	public function setScript($script) {
		return $this->setParam('script', $script);
	}

	/**
	 * Add a param
	 *
	 * @param array $param key value
	 * @return Elastica_Query_CustomScore
	 */
	public function addParam(array $param) {
		$this->addParam('params', $param);
		return $this;
	}
}

