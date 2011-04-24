<?php
/**
 * Terms query
 *
 * @uses Elastica_Query_Abstract
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/terms-query.html
 */
class Elastica_Query_Terms extends Elastica_Query_Abstract
{
	protected $_terms = array();
	protected $_params = array();
	protected $_key = '';

	/**
	 * @param string $key OPTIONAL Terms key
	 * @param array $terms OPTIONLA Terms list
	 */
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

	/**
	 * Adds a single term to the list
	 *
	 * @param string $term Term
	 */
	public function addTerm($term) {
		$this->_terms[] = $term;
		return $this;
	}

	/**
	 * Sets a general parameter for the terms query array
	 *
	 * This can be used to set not supported params
	 *
	 * @param string $key Key to set
	 * @param mixed $value Value to key
	 */
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

	/**
	 * Converts the terms object to an array
	 *
	 * @return array Query array
	 * @see Elastica_Query_Abstract::toArray()
	 */
	public function toArray() {
		if (empty($this->_key)) {
			throw new Elastica_Exception_Invalid('Terms key has to be set');
		}
		$this->_params[$this->_key] = $this->_terms;
		return array('terms' => $this->_params);
	}
}