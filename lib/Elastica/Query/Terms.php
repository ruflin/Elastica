<?php
/**
 * Terms query
 *
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/terms-query.html
 * @uses Elastica_Query_Abstract
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class Elastica_Query_Terms extends Elastica_Query_Abstract
{
	protected $_terms = array();
	protected $_params = array();
	protected $_key = '';

	public function __construct($key = '', array $terms = array()) {
		$this->setTerms($key, $terms);
	}

	/**
	 * Sets key and terms for the query
	 *
	 * @param string $key Terms key
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
		if (empty($this->_key)) {
			throw new Elastica_Exception_Invalid('Terms key has to be set');
		}
		$this->_params[$this->_key] = $this->_terms;
		return array('terms' => $this->_params);
	}
}