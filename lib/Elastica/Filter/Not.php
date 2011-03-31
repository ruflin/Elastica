<?php
/**
 * Not Filter
 *
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/not-filter.html
 * @uses Elastica_Filter_Abstract
 * @package Elastica
 * @author Lee Parker
 */
class Elastica_Filter_Not extends Elastica_Filter_Abstract
{

	protected $_filter = '';

	public function __construct(Elastica_Filter_Abstract $filter) {
		$this->_filter = $filter->toArray();
	}

	public function toArray() {
		return array('not' => array('filter' => $this->_filter));
	}
}
