<?php
/**
 * Range Filter
 *
 * @link http://www.elasticsearch.com/docs/elasticsearch/rest_api/query_dsl/range_query/
 * @uses Elastica_Filter_Abstract
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class Elastica_Filter_Range extends Elastica_Filter_Abstract
{
	protected $_fields = array();

	public function addField($fieldName, array $args) {
		$this->_fields[$fieldName] = $args;
	}
	
	public function toArray() {
		$args = $this->_fields;
		return array('range' => $args);
	}
}
