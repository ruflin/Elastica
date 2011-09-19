<?php
/**
 * Wildcard query
 *
 * @uses Elastica_Query_Abstract
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @link http://www.elasticsearch.com/docs/elasticsearch/rest_api/query_dsl/wildcard_query
 */
class Elastica_Query_Wildcard extends Elastica_Query_Abstract
{

	/**
	 * @param string $key OPTIONAL Wildcard key
	 * @param string $value OPTIONAL Wildcard value
	 * @param float $boost OPTIONAL Boost value (default = 1)
	 */
	public function __construct($key = '', $value = null, $boost = 1.0) {
		if (!empty($key)) {
			$this->setValue($key, $value, $boost);
		}
	}

	/**
	 * Sets the query expression for a key with its boost value
	 *
	 * @param string $key
	 * @param string $value
	 * @param float $boost
	 */
	public function setValue($key, $value, $boost = 1.0) {
		$this->setParam($key, array('value' => $value, 'boost' => $boost));
	}
}
