<?php
/**
 * Bool query
 *
 * @uses Elastica_Query_Abstract
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @link http://www.elasticsearch.com/docs/elasticsearch/rest_api/query_dsl/bool_query/
 */
class Elastica_Query_Bool extends Elastica_Query_Abstract
{
	protected $_boost = 1.0;
	protected $_minimumNumberShouldMatch = 1;

	protected $_must = array();
	protected $_should = array();
	protected $_mustNot = array();

	/**
	 * Add should part to query
	 *
	 * @param Elastica_Query_Abstract|array $args Should query
	 * @return Elastica_Query_Bool Current object
	 */
	public function addShould($args) {
		return $this->_addQuery('should', $args);
	}

	/**
	 * Add must part to query
	 *
	 * @param Elastica_Query_Abstract|array $args Must query
	 * @return Elastica_Query_Bool Current object
	 */
	public function addMust($args) {
		return $this->_addQuery('must', $args);
	}

	/**
	 * Add must not part to query
	 *
	 * @param Elastica_Query_Abstract|array $args Must not query
	 * @return Elastica_Query_Bool Current object
	 */
	public function addMustNot($args) {
		return $this->_addQuery('mustNot', $args);
	}

	/**
	 * Adds a query to the current object
	 *
	 * @param string $type Query type
	 * @param Elastica_Query_Abstract|array $args Query
	 * @throws Elastica_Exception_Invalid If not valid query
	 */
	public function _addQuery($type, $args) {
		if ($args instanceof Elastica_Query_Abstract) {
			$args = $args->toArray();
		}

		if (!is_array($args)) {
			throw new Elastica_Exception_Invalid('Invalid parameter. Has to be array or instance of Elastica_Query');
		}

		$varName = '_' . $type;
		$this->{$varName}[] = $args;
		return $this;
	}

	/**
	 * Converts query to an array
	 *
	 * @return array Data array
	 * @see Elastica_Query_Abstract::toArray()
	 */
	public function toArray() {
		$args = array();

		if (!empty($this->_must)) {
			$args['must'] = $this->_must;
		}

		if (!empty($this->_should)) {
			$args['should'] = $this->_should;
			$args['minimum_number_should_match'] = $this->_minimumNumberShouldMatch;
		}

		if (!empty($this->_mustNot)) {
			$args['must_not'] = $this->_mustNot;
		}

		$args['boost'] = $this->_boost;

		return array('bool' => $args);
	}

	/**
	 * Sets boost value of this query
	 *
	 * @param float $boost Boost value
	 * @return Elastica_Query_Bool Current object
	 */
	public function setBoost($boost) {
		$this->_boost = $boost;
		return $this;
	}

	/**
	 * Set the minimum number of of should match
	 *
	 * @param int $minimumNumberShouldMatch Should match minimum
	 * @return Elastica_Query_Bool Current object
	 */
	public function setMinimumNumberShouldMatch($minimumNumberShouldMatch) {
		$this->_minimumNumberShouldMatch = (int) $minimumNumberShouldMatch;
		return $this;
	}

}
