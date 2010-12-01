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
class Elastica_Filter_Terms extends Elastica_Filter_Abstract
{
	protected $_terms = array();
	
	public function __construct(array $terms = array()) {
		$this->setTerms($terms);
	}
	
	public function setTerms(array $terms) {
		$this->_terms = $terms;
	}
	
	/**
	 * Adds a term to the term query
	 * 
	 * @param string $key Key to query
	 * @param array $value Values(s) for the query. Boost can be set with array
	 */
	public function addTerm($key, array $value) {
		$this->_terms = array($key => array_values($value));
	}
	
	public function toArray() {
		$args = $this->_terms;
		
		return array('terms' => $args);
	}
}
