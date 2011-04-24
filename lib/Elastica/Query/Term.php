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
class Elastica_Query_Term extends Elastica_Query_Abstract
{
	protected $_term = array();

	/**
	 * Constructs the Term query object
	 *
	 * @param array $term OPTIONAL Calls setTerm with the given $term array
	 */
	public function __construct(array $term = array()) {
		$this->setTerm($term);
	}

	/**
	 * Set term can be used instead of addTerm if some more special
	 * values for a term have to be set.
	 *
	 * @param array $term Term array
	 * @return Elastica_Query_Term Current object
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
	 * @param float $boost OPTIONAL Boost value (default = 1.0)
	 * @return Elastica_Query_Term Current object
	 */
	public function addTerm($key, $value, $boost = 1.0) {
		// TODO: Why is return $this->setTerm(array($key => array('value' => $value, 'boost' => $boost)));
		// 		not working? Tested with filer
		return $this->setTerm(array($key => $value));
	}

	/**
	 * Converts the term query to an array
	 *
	 * @return array Array term query
	 */
	public function toArray() {
		$args = $this->_term;
		return array('term' => $args);
	}
}
