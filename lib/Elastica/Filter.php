<?php
/**
 * Filter object
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class Elastica_Filter
{
	protected $_query;
	protected $_filter;

	public function __construct(Elastica_Filter_Abstract $filter) {
		$this->setFilter($filter);
	}

	public function setFilter(Elastica_Filter_Abstract $filter) {
		$this->_filter = $filter;
		return $this;
	}

	public function toArray() {
		return array('filter' => $this->_filter->toArray());
	}
}
