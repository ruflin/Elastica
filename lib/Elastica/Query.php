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
	const TERM = 'term';
	const RANGE = 'range';
	const WILDCARD = 'wildcard';
	const QUERY_STRING = 'query_string';
	
	protected $_rawArguments = array();
	protected $_from = 0;
	protected $_limit = 10;
	protected $_sortArgs = array();
	protected $_explain = false;
	protected $_fileds = array();
	protected $_scriptFields = array();
	protected $_queries = array();
	protected $_filters = array();
	
	public function addQuery(Elastica_Query_Abstract $query) {
		$this->_queries = $query->toArray();
	}
	
	public function addFilter(Elastica_Filter $filter) {
		$this->_filters = $filter->toArray();
	}
	
	public function setFrom() {
		$this->_form = 0;
	}
	
	/**
	 * Sets sort arguments for the query
	 * 
	 * @link http://www.elasticsearch.com/docs/elasticsearch/rest_api/search/sort/
	 * @param array $sortArgs Sorting arguments
	 */
	public function setSort(array $sortArgs) {
		$this->_sortArgs = $sortArgs;
	}
	
	/**
	 * Sets highlight arguments for the query
	 * 
	 * @link http://www.elasticsearch.com/docs/elasticsearch/rest_api/search/highlighting/
	 * @param array $highlightArgs
	 */
	public function setHighlight(array $highlightArgs) {
		$this->_highlightArgs = $highlightArgs;
	}
	
	/**
	 * Alias for setLimit
	 * 
	 * @param int $limit OPTIONAL Maximal number of results for query (default = 10)
	 */
	public function setSize($limit = 10) {
		$this->setLimit();
	}
	
	/**
	 * Sets maximum number of results for this query
	 * 
	 * @param int $limit OPTIONAL Maximal number of results for query (default = 10)
	 */
	public function setLimit($limit = 10) {
		$this->_limit = $limit;
	}
	
	/**
	 * Enables explain on the query
	 * 
	 * @link http://www.elasticsearch.com/docs/elasticsearch/rest_api/search/explain/
	 * @param bool $explain OPTIONAL Enabled or disable explain (default = true)
	 */
	public function setExplain($explain = true) {
		$this->_explain = $explain;
	}
	
	/**
	 * Sets the fields to be returned by the search
	 * 
	 * @link http://www.elasticsearch.com/docs/elasticsearch/rest_api/search/fields/
	 * @param array $fields Fields to be returne
	 */
	public function setFields(array $fields) {
		$this->_fields = $fields;
	}
	
	/**
	 * Set script fields
	 * 
	 * @link http://www.elasticsearch.com/docs/elasticsearch/rest_api/search/script_fields/
	 * @param array $scriptFields Script fields
	 */
	public function setScriptFields(array $scriptFields) {
		$this->_scriptFields = $scriptFields;
	}
	
	/**
	 * Allows to set raw arguments that can't be set over the
	 * provided method. Field name has also to be set in given array.
	 * Values set here are overrided by values set
	 * over the specific methods
	 * 
	 * @param array $args Argument array
	 */
	public function setRawArguments(array $args) {
		$this->_rawArgumens = $args;
	}
	
	/**
	 * @link http://www.elasticsearch.com/docs/elasticsearch/rest_api/search/facets
	 */
	public function setFacets(array $args) {
		throw new Elastica_Exception('not implemented yet');
	}
	
	public function toArray() {
		
		$query = $this->_rawArguments;

		$query['query'] = $this->_queries;
		$query['size'] = $this->_limit;
		$query['from'] = $this->_from;
		
		if (!empty($this->_sortArgs)) {
			$query['sort'] = $this->_sortArgs;
		}
		
		if (!empty($this->_highlightArgs)) {
			$query['highlight'] = $this->_highlightArgs;
		}
		
		if ($this->_explain) {
			$query['explain'] = $this->_explain;
		}
		
		if (!empty($this->_fields)) {
			$query['fields'] = $this->_fields;
		}
		
		if (!empty($this->_scriptFields)) {
			$query['script_fields'] = $this->_scriptFields;
		}
		
		if (!empty($this->_filters)) {
			// TODO: should query really be overwritten?
			$query['query'] = $this->_filters;			
		}
		
		return $query;
	}
}