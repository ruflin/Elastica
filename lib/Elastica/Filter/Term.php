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
	protected $_term = array();

	/**
	 * @param array $term Term array
	 */
	public function __construct(array $term = array()) {
		$this->setTerm($term);
	}

	/**
	 * Sets/overwrites key and term directly
	 *
	 * @param array $term Key value pair
	 * @return Elastica_Filter_Term Filter object
	 */
	public function setTerm(array $term) {
		$this->_term = $term;
		return $this;
	}

	/**
	 * Adds a term to the term query
	 *
	 * @param string $key Key to query
	 * @param string|array $value Values(s) for the query. Boost can be set with array
	 * @return Elastica_Filter_Term Filter object
	 */
	public function addTerm($key, $value) {
		$this->_term = array($key => $value);
		return $this;
	}

	/**
	 * Convers filter to array
	 *
	 * @see Elastica_Filter_Abstract::toArray()
	 * @return array Data array
	 */
	public function toArray() {
		$args = $this->_term;

		return array('term' => $args);
	}
}
