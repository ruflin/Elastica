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
class Elastica_Query_ConstantScore extends Elastica_Query_Abstract {

	protected $_boost = null;

	/**
	 * @var array|Elastica_Filter_Abstract
	 */
	protected $_filter = null;


	/**
	 * @param array|Elastica_Filter_Abstract $filter
	 * @return Elastica_Query_ConstantScore Query object
	 * @throws InvalidArgumentException
	 */
	public function setFilter($filter) {
		if (!is_array($filter) && !$filter instanceof Elastica_Filter_Abstract) {
			throw new InvalidArgumentException('expected an array or Elastica_Filter_Abstract');
		}
		$this->_filter = $filter;
		return $this;
	}

	/**
	 * @return array Query array
	 * @see Elastica_Query_Abstract::toArray()
	 */
	public function toArray() {
		$ret = array(
			'constant_score' => array(
				'filter' => (
					$this->_filter instanceof Elastica_Filter_Abstract
					? $this->_filter->toArray()
					: $this->_filter
				),
			)
		);

		if (! is_null($this->_boost)) {
			$ret['boost'] = $this->_boost;
		}

		return $ret;
	}
}

