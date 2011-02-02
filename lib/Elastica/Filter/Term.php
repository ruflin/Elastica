<?php
/**
 * Term query
 *
 * @link http://www.elasticsearch.com/docs/elasticsearch/rest_api/query_dsl/term_query/
 * @uses Elastica_Query_Abstract
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class Elastica_Filter_Term extends Elastica_Filter_Abstract
{
	protected $_term = array();

	public function __construct(array $term = array()) {
		$this->setTerm($term);
	}

	public function setTerm(array $term) {
		$this->_term = $term;
	}

	/**
	 * Adds a term to the term query
	 *
	 * @param string $key Key to query
	 * @param string|array $value Values(s) for the query. Boost can be set with array
	 */
	public function addTerm($key, $value) {
		$this->_term = array($key => $value);
	}

	public function toArray() {
		$args = $this->_term;

		return array('term' => $args);
	}
}
