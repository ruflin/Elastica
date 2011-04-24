<?php
/**
 * Fuzzy query
 *
 * @uses Elastica_Query_Abstract
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @link http://www.elasticsearch.com/docs/elasticsearch/rest_api/query_dsl/fuzzy_query/
 */
class Elastica_Query_Fuzzy extends Elastica_Query_Abstract
{
	protected $_fields = array();

	/**
	 * Adds field to fuzzy query
	 *
	 * @param string $fieldName Field name
	 * @param array $args Data array
	 * @return Elastica_Query_Fuzzy Current object
	 */
	public function addField($fieldName, array $args) {
		$this->_fields[$fieldName] = $args;
		return $this;
	}

	/**
	 * Converts fuzzy query to array
	 *
	 * @return array Query array
	 * @see Elastica_Query_Abstract::toArray()
	 */
	public function toArray() {
		$args = $this->_fields;
		return array('fuzzy' => $args);
	}
}
