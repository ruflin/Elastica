<?php
/**
 * Elastica query object
 *
 * Creates different types of queries
 *
 * @link http://www.elasticsearch.com/docs/elasticsearch/rest_api/search/
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class Elastica_Query
{
	protected $_query = array();

	/**
	 * Creates a query object
	 *
	 * @param array|Elastica_Query_Abstract $query OPTIONAL Query object (default = null)
	 */
	public function __construct($query = null) {
		if (is_array($query)) {
			$this->setRawQuery($query);
		} else if ($query instanceof Elastica_Query_Abstract) {
			$this->setQuery($query);
		}
	}

	/**
	 * Sets query as raw array. Will overwrite all already set arguments
	 *
	 * @param array $query Query array
	 * @return Elastica_Query Query object
	 */
	public function setRawQuery(array $query) {
		$this->_query = $query;
		return $this;
	}

	/**
	 * Sets a single param for the query
	 *
	 * @param string $key Key to set
	 * @param mixed $value Value
	 */
	public function setParam($key, $value) {
		$this->_query[$key] = $value;
		return $this;
	}

	/**
	 * Sets the query
	 *
	 * @param Elastica_Query_Abstract $query Query object
	 * @return Elastica_Query Query object
	 */
	public function setQuery(Elastica_Query_Abstract $query) {
		return $this->setParam('query', $query->toArray());
	}

	/**
	 * @deprecated Use setQuery
	 */
	public function addQuery(Elastica_Query_Abstract $query) {
		trigger_error('addQuery is deprecated. Use setQuery instead');
		$this->setQuery($query);
	}

	/**
	 * @param Elastica_Filter $filter Filter object
	 * @return Elastica_Query Current object
	 */
	public function setFilter(Elastica_Filter_Abstract $filter) {
		return $this->setParam('filter', $filter->toArray());
	}

	public function setFrom($from) {
		return $this->setParam('from', $from);
	}

	/**
	 * Sets sort arguments for the query
	 * Replaces existing values
	 *
	 * @link http://www.elasticsearch.com/docs/elasticsearch/rest_api/search/sort/
	 * @param array $sortArgs Sorting arguments
	 * @return Elastica_Query Query object
	 */
	public function setSort(array $sortArgs) {
		return $this->setParam('sort', $sortArgs);
	}

	/**
	 * Adds a sort param to the query
	 *
	 * @todo Test
	 * @param mixed $sort Sort parameter
	 * @return Elastica_Query Query object
	 */
	public function addSort($sort) {
		$this->_query['sort'][] = $sort;
		return $this;
	}

	/**
	 * Sets highlight arguments for the query
	 *
	 * @link http://www.elasticsearch.com/docs/elasticsearch/rest_api/search/highlighting/
	 * @param array $highlightArgs Set all highlight arguments
	 * @return Elastica_Query Query object
	 */
	public function setHighlight(array $highlightArgs) {
		return $this->setParam('highlight', $highlightArgs);
	}

	/**
	 * Adds a highlight argument
	 *
	 * @link http://www.elasticsearch.com/docs/elasticsearch/rest_api/search/highlighting/
	 * @param mixed $highlightArg Add highlight argument
	 * @return Elastica_Query Query object
	 */
	public function addHighlight($highlight) {
		$this->_query['highligth'][] = $highlight;
		return $this;
	}

	/**
	 * Alias for setLimit
	 *
	 * @param int $limit OPTIONAL Maximal number of results for query (default = 10)
	 * @return Elastica_Query Query object
	 */
	public function setSize($limit = 10) {
		return $this->setLimit($limit);
	}

	/**
	 * Sets maximum number of results for this query
	 *
	 * @param int $limit OPTIONAL Maximal number of results for query (default = 10)
	 * @return Elastica_Query Query object
	 */
	public function setLimit($limit = 10) {
		return $this->setParam('limit', $limit);
	}

	/**
	 * Enables explain on the query
	 *
	 * @link http://www.elasticsearch.com/docs/elasticsearch/rest_api/search/explain/
	 * @param bool $explain OPTIONAL Enabled or disable explain (default = true)
	 */
	public function setExplain($explain = true) {
		return $this->setParam('explain', $explain);
	}

	/**
	 * Sets the fields to be returned by the search
	 *
	 * @link http://www.elasticsearch.com/docs/elasticsearch/rest_api/search/fields/
	 * @param array $fields Fields to be returne
	 */
	public function setFields(array $fields) {
		return $this->setParam('fields', $fields);
	}

	/**
	 * Set script fields
	 *
	 * @link http://www.elasticsearch.com/docs/elasticsearch/rest_api/search/script_fields/
	 * @param array $scriptFields Script fields
	 */
	public function setScriptFields(array $scriptFields) {
		return $this->setParam('script_field', $scriptFields);
	}

	/**
	 * @link http://www.elasticsearch.com/docs/elasticsearch/rest_api/search/facets
	 */
	public function setFacets(Elastica_Facets $facets) {
		return $this->setParam('facets', $facets->toArray());
	}

	public function addFacet(Elastica_Facet_Abstract $facet) {
		$this->_query['facets'][$facet->getName()] = $facet->toArray();
		return $this;
	}

	public function toArray() {

		if (!isset($this->_query['query'])) {
			$this->setQuery(new Elastica_Query_MatchAll());
		}

		return $this->_query;
	}
}