<?php
/**
 * And Filter
 *
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/and-filter.html
 * @uses Elastica_Filter_Abstract
 * @package Elastica
 * @author Lee Parker
 */
class Elastica_Filter_And extends Elastica_Filter_Abstract
{

	protected $_filters = array();

	public function addFilter(Elastica_Filter_Abstract $filter) {
		$this->_filters[] = $filter->toArray();
	}

	public function toArray() {
		return array('and' => $this->_filters);
	}
}
