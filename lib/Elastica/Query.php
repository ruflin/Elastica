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
	 * Transforms a string or an array to a query object
	 *
	 * @param mixed $query
	 * @return Elastica_Query
	 **/
	public static function create($query) {
		switch (true) {
			case $query instanceof Elastica_Query:
				return $query;
			case $query instanceof Elastica_Query_Abstract:
				return new self($query);
			case is_string($query):
				return new self(new Elastica_Query_QueryString($query));
			case empty($query):
				return new self();
		}

		// TODO: Implement queries without
		throw new Elastica_Exception_NotImplemented();
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
	 * Gets the query array
	 *
	 * @return array
	 **/
	public function getQuery() {
		return $this->_query['query'];
	}

	/**
	 * @param Elastica_Filter_Abstract $filter Filter object
	 * @return Elastica_Query Current object
	 */
	public function setFilter(Elastica_Filter_Abstract $filter) {
		return $this->setParam('filter', $filter->toArray());
	}

	/**
	 * Sets the start from which the search results should be returned
	 *
	 * @param int $from
	 * @return Elastica_Query Query object
	 */
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
	 * @param array $highlightArgs Set all highlight arguments
	 * @return Elastica_Query Query object
	 * @link http://www.elasticsearch.org/guide/reference/api/search/highlighting.html
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
		$this->_query['highlight'][] = $highlight;
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
		return $this->setParam('size', $limit);
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
	 * Enables version on the query
	 *
	 * @link http://www.elasticsearch.com/docs/elasticsearch/rest_api/search/version/
	 * @param bool $version OPTIONAL Enabled or disable version (default = true)
	 */
	public function setVersion($version = true) {
		return $this->setParam('version', $version);
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
	 * Sets all facets for this query object. Replaces existing facets
	 *
	 * @link http://www.elasticsearch.com/docs/elasticsearch/rest_api/search/facets
	 * @param array $facets List of facet objects
	 * @return Elastica_Query Query object
	 */
	public function setFacets(array $facets) {
		$this->_query['facets'] = array();
		foreach ($facets as $facet) {
			$this->addFacet($facet);
		}
		return $this;
	}

	/**
	 * Adds a Facet to the query
	 *
	 * @param Elastica_Facet_Abstract $facet Facet object
	 * @return Elastica_Query Query object
	 */
	public function addFacet(Elastica_Facet_Abstract $facet) {
		$this->_query['facets'][$facet->getName()] = $facet->toArray();
		return $this;
	}

	/**
	 * Converts all query params to an array
	 *
	 * @return array Query array
	 */
	public function toArray() {

		// If no query is set, all query is chosen by default
		if (!isset($this->_query['query'])) {
			$this->setQuery(new Elastica_Query_MatchAll());
		}

		return $this->_query;
	}
}
