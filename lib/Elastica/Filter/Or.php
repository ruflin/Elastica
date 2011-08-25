<?php
/**
 * Or Filter
 *
 * @uses Elastica_Filter_Abstract
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @link http://www.elasticsearch.com/docs/elasticsearch/rest_api/query_dsl/or_filter/
 */
class Elastica_Filter_Or extends Elastica_Filter_Abstract
{
	protected $_filters = array();

	/**
	 * Adds filter to or filter
	 *
	 * @param Elastica_Filter_Abstract $filter Filter object
	 * @return Elastica_Filter_Or Filter object
	 */
	public function addFilter(Elastica_Filter_Abstract $filter) {
		$this->_filters[] = $filter->toArray();
		return $this;
	}

	/**
	 * Convers current object to array.
	 *
	 * @see Elastica_Filter_Abstract::toArray()
	 * @return array Or array
	 */
	public function toArray() {
		$this->setParams($this->_filters);
		return parent::toArray();
	}
}
