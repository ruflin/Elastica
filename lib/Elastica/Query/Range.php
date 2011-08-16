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
	/**
	 * Adds a range field to the query
	 *
	 * @param string $fieldName Field name
	 * @param array $args Field arguments
	 * @return Elastica_Query_Range Current object
	 */
	public function addField($fieldName, array $args) {
		return $this->setParam($fieldName, $args);

	}
}
