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
	protected $_minimumMatch = '';

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
	 * @param string|array $value Values(s) for the query. Boost can be set with array
	 */
	public function addTerm($key, array $value) {
		$this->_terms = array($key => array_values($value));
	}
	
	/**
	 * Sets the minimum matching values
	 *
	 * @param int $minimum Minimum value
	 */
	public function setMinimumMatch($minimum) {
		$this->_minimumMatch = $minimum;
		return $this;
	}

	public function toArray() {
		$args = $this->_terms;
		
		if(!empty($this->_minimumMatch)) {
			$args['minimum_match'] = $this->_minimumMatch;
		}
		
		return array('terms' => $args);
	}
}