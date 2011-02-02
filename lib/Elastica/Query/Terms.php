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
	public function addTerm($key, $value) {
		$this->_terms[] = array($key => $value);
	}

	public function setMinimumMatch() {
	}

	public function toArray() {
		throw new Elastica_Exception_NotImplemented();
	}
}