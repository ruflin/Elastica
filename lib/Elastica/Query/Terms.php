<?php
/**
 * Terms query
 *
 * @link http://www.elasticsearch.com/docs/elasticsearch/rest_api/query_dsl/terms_query/
 * @uses Elastica_Query_Abstract
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class Elastica_Query_Terms extends Elastica_Query_Abstract
{
	protected $_terms = array();
	protected $_params = array();

	public function __construct($key = '', array $terms = array()) {
		$this->setTerms($key, $terms);
	}

	/**
	 * Adds a term to the term query
	 *
	 * @param string $key Key to query
	 * @param array $terms Terms for the query.
	 */
	public function setTerms($key, array $terms) {
		$this->_key = $key;
		$this->_terms = array_values($terms);
		return $this;
	}

	public function addTerm($term) {
		$this->_terms[] = $term;
		return $this;
	}

	public function setParam($key, $value) {
		$this->_params[$key] = $value;
		return $this;
	}

	/**
	 * Sets the minimum matching values
	 *
	 * @param int $minimum Minimum value
	 */
	public function setMinimumMatch($minimum) {
		return $this->setParam('minimum_match', (int) $minimium);
	}

	public function toArray() {
		$this->_params[$this->_key] = $this->_terms;
		return array('terms' => $this->_params);
	}
}