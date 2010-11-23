<?php
/**
 * Fuzzy query
 *
 * @link http://www.elasticsearch.com/docs/elasticsearch/rest_api/query_dsl/fuzzy_query/
 * @uses Elastica_Query_Abstract
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class Elastica_Query_Fuzzy extends Elastica_Query_Abstract
{
	protected $_fields = array();
	
	public function addField($fieldName, array $args) {
		$this->_fields[$fieldName] = $args;
	}
	
	public function toArray() {
		$args = $this->_fields;
		return array('fuzzy' => $args);
	}
}
