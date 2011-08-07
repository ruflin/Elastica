<?php
/**
 * Exists query
 *
 * @uses Elastica_Query_Abstract
 * @category Xodoa
 * @package Elastica
 * @author Oleg Cherniy <oleg.cherniy@gmail.com>
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/exists-filter.html
 */
class Elastica_Filter_Exists extends Elastica_Filter_Abstract
{
	/**
	 * @param string $field
	 */
	public function __construct($field) {
		$this->setField($field);
	}

	/**
	 * @param string $field
	 * @return Elastica_Filter_Exists
	 */
	public function setField($field) {
		return $this->setParam('field', $field);
	}
}
