<?php
/**
 * Filtered query. Needs a query and a filter
 *
 * @link http://www.elasticsearch.com/docs/elasticsearch/rest_api/query_dsl/fuzzy_query/
 * @uses Elastica_Query_Abstract
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class Elastica_Query_Filtered extends Elastica_Query_Abstract
{
	protected $_query = null;
	protected $_filter = null;

	public function __construct(Elastica_Query_Abstract $query, Elastica_Filter_Abstract $filter) {
		$this->setQuery($query);
		$this->setFilter($filter);
	}

	public function setQuery(Elastica_Query_Abstract $query) {
		$this->_query = $query;
	}

	public function setFilter(Elastica_Filter_Abstract $filter) {
		$this->_filter = $filter;
	}

	public function toArray() {
		return array('filtered' => array(
			'query' => $this->_query->toArray(),
			'filter' => $this->_filter->toArray()
		));
	}
}
