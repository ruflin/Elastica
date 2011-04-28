<?php
/**
 * Not Filter
 *
 * @uses Elastica_Filter_Abstract
 * @category Xodoa
 * @package Elastica
 * @author Lee Parker, Nicolas Ruflin <spam@ruflin.com>
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/not-filter.html
 */
class Elastica_Filter_Not extends Elastica_Filter_Abstract
{
	protected $_filter = '';

	/**
	 * Creates Not filter query
	 *
	 * @param Elastica_Filter_Abstract $filter Filter object
	 */
	public function __construct(Elastica_Filter_Abstract $filter) {
		$this->_filter = $filter->toArray();
	}

	/**
	 * Convers not filter to array
	 *
	 * @see Elastica_Filter_Abstract::toArray()
	 * @return array Not filter as array
	 */
	public function toArray() {
		return array('not' => array('filter' => $this->_filter));
	}
}
