<?php
/**
 * Range Filter
 *
 * @uses Elastica_Filter_Abstract
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @link http://www.elasticsearch.com/docs/elasticsearch/rest_api/query_dsl/range_query/
 */
class Elastica_Filter_Range extends Elastica_Filter_Abstract
{
	protected $_fields = array();

	/**
	 * Ads a field with arguments to the range query
	 *
	 * @param string $fieldName Field name
	 * @param array $args Field arguments
	 */
	public function addField($fieldName, array $args) {
		$this->_fields[$fieldName] = $args;
	}

	/**
	 * Convers object to array
	 *
	 * @see Elastica_Filter_Abstract::toArray()
	 * @return array Filter array
	 */
	public function toArray() {
		$args = $this->_fields;
		return array('range' => $args);
	}
}
