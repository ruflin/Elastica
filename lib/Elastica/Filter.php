<?php
/**
 * Filter object
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @todo Not implemented yet
 */
class Elastica_Filter
{
	protected $_query;
	protected $_filter;

	public function __construct(Elastica_Filter_Abstract $filter = null, Elastica_Query_Abstract $query = null) {
		$this->_query = $query;
		$this->_filter = $filter;
	}

	public function addFilter(Elastica_Filter_Abstract $filter) {
		$this->_filter = $filter;
	}

	public function addQuery(Elastica_Query_Abstract $query) {
		$this->_query = $query;
	}

	public function toArray() {
		return array('filtered' => array(
			'query' => $this->_query->toArray(),
			'filter' => $this->_filter->toArray()
		));
	}
}
