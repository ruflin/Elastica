<?php
/**
 * Field query
 *
 * @uses Elastica_Query_Abstract
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @link http://www.elasticsearch.com/docs/elasticsearch/rest_api/query_dsl/field_query/
 */
class Elastica_Query_Field extends Elastica_Query_Abstract
{
	/**
	 * Converts query to array
	 *
	 * @return array Query array
	 * @see Elastica_Query_Abstract::toArray()
	 */
	public function toArray() {
		return array('field' => $args);
	}
}
