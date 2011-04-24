<?php
/**
 * Range query
 *
 * @uses Elastica_Query_Abstract
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @link http://www.elasticsearch.com/docs/elasticsearch/rest_api/query_dsl/range_query/
 */
class Elastica_Query_Range extends Elastica_Query_Abstract
{
	protected $_fields = array();

	/**
	 * Adds a range field to the query
	 *
	 * @param string $fieldName Field name
	 * @param array $args Field arguments
	 * @return Elastica_Query_Range Current object
	 */
	public function addField($fieldName, array $args) {
		$this->_fields[$fieldName] = $args;
		return $this;
	}

	/**
	 * Converst the range query to an array
	 *
	 * @return array Query array
	 * @see Elastica_Query_Abstract::toArray()
	 */
	public function toArray() {
		$args = $this->_fields;
		return array('range' => $args);
	}
}
