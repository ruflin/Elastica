<?php
/**
 * Or Filter
 *
 * @link http://www.elasticsearch.com/docs/elasticsearch/rest_api/query_dsl/or_filter/
 * @uses Elastica_Filter_Abstract
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class Elastica_Filter_Or extends Elastica_Filter_Abstract
{

	protected $_queries = array();

	public function addQuery(Elastica_Query_Abstract $query) {
		$this->_queries[] = $query->toArray();
	}

	public function toArray() {
		return array('or' => $this->_queries);
	}
}
