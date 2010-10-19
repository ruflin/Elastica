<?php
/**
 * Wildcard query
 *
 * @link http://www.elasticsearch.com/docs/elasticsearch/rest_api/query_dsl/wildcard_query
 * @uses Elastica_Query_Abstract
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class Elastica_Query_Wildcard extends Elastica_Query_Abstract
{
	/**
	 * Returns query in form of an array
	 * 
	 * @return array Query as array
	 */
	public function toArray() {		 
		return array('wildcard' => $args);
	}
}
