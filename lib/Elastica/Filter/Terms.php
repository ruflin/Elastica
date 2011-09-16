<?php
/**
 * Terms filter
 *
 * @uses Elastica_Query_Abstract
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/terms-filter.html
 */
class Elastica_Filter_Terms extends Elastica_Filter_Abstract
{
	protected $_terms = array();
	protected $_params = array();
	protected $_key = '';

	/**
	 * Creates terms filter
	 *
	 * @param string $key Terms key
	 * @param array $terms Terms values
	 */
	public function __construct($key = '', array $terms = array()) {
		$this->setTerms($key, $terms);
	}

	/**
	 * Sets key and terms for the filter
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
	 * Adds an additional term to the query
	 *
	 * @param string $term Filter term
	 * @return Elastica_Filter_Abstract Filter object
	 */
	public function addTerm($term) {
		$this->_terms[] = $term;
		return $this;
	}

	/**
	 * Convers object to an arrray
	 *
	 * @see Elastica_Filter_Abstract::toArray()
	 * @return array data array
	 */
	public function toArray() {
		if (empty($this->_key)) {
			throw new Elastica_Exception_Invalid('Terms key has to be set');
		}
		$this->_params[$this->_key] = $this->_terms;
		return array('terms' => $this->_params);
	}
}
