<?php
/**
 * Filtered query. Needs a query and a filter
 *
 * @uses Elastica_Query_Abstract
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @link http://www.elasticsearch.com/docs/elasticsearch/rest_api/query_dsl/fuzzy_query/
 */
class Elastica_Query_Filtered extends Elastica_Query_Abstract
{
	protected $_query = null;
	protected $_filter = null;

	/**
	 * Constructs a filtered query
	 *
	 * @param Elastica_Query_Abstract $query Query object
	 * @param Elastica_Filter_Abstract $filter Filter object
	 */
	public function __construct(Elastica_Query_Abstract $query, Elastica_Filter_Abstract $filter) {
		$this->setQuery($query);
		$this->setFilter($filter);
	}

	/**
	 * Sets a query
	 *
	 * @param Elastica_Query_Abstract $query Query object
	 * @return Elastica_Query_Filtered Current object
	 */
	public function setQuery(Elastica_Query_Abstract $query) {
		$this->_query = $query;
		return $this;
	}

	/**
	 * Sets the filter
	 *
	 * @param Elastica_Filter_Abstract $filter Filter object
	 * @return Elastica_Query_Filtered Current object
	 */
	public function setFilter(Elastica_Filter_Abstract $filter) {
		$this->_filter = $filter;
		return $this;
	}

	/**
	 * Converts query to array
	 *
	 * @return array Query array
	 * @see Elastica_Query_Abstract::toArray()
	 */
	public function toArray() {
		return array('filtered' => array(
			'query' => $this->_query->toArray(),
			'filter' => $this->_filter->toArray()
		));
	}
}
