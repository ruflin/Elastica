<?php
/**
 * Term query
 *
 * @uses Elastica_Query_Abstract
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @link http://www.elasticsearch.com/docs/elasticsearch/rest_api/query_dsl/term_query/
 */
class Elastica_Filter_Term extends Elastica_Filter_Abstract
{
	/**
	 * @param array $term Term array
	 */
	public function __construct(array $term = array()) {
		$this->setRawTerm($term);
	}

	/**
	 * Sets/overwrites key and term directly
	 *
	 * @param array $term Key value pair
	 * @return Elastica_Filter_Term Filter object
	 */
	public function setRawTerm(array $term) {
		return $this->setParams($term);
	}

	/**
	 * Adds a term to the term query
	 *
	 * @param string $key Key to query
	 * @param string|array $value Values(s) for the query. Boost can be set with array
	 * @return Elastica_Filter_Term Filter object
	 */
	public function setTerm($key, $value) {
		return $this->setRawTerm(array($key => $value));
	}
}
