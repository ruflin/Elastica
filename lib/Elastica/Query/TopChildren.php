<?php

/**
 * Runs the child query with an estimated hits size, and out of the hit docs, aggregates it into parent docs.
 *
 * @uses Elastica_Query_Abstract
 * @category Xodoa
 * @package Elastica
 * @author Wu Yang <darkyoung@gmail.com>
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/top-children-query.html
 */
class Elastica_Query_TopChildren extends Elastica_Query_Abstract
{
	/**
	 * @param string|Elastica_Query $query Query string or a Elastica_Query object
	 * @param string $type Parent document type
	 */
	public function __construct($query, $type = null) {
		$this->setQuery($query);
		$this->setType($type);
	}

	/**
	 * Sets query object
	 *
	 * @param string|Elastica_Query|Elastica_Query_Abstract $query
	 * @return Elastica_Query_TopChildren
	 */
	public function setQuery($query) {
		$query = Elastica_Query::create($query);
		$data = $query->toArray();
		return $this->setParam('query', $data['query']);
	}

	/**
	 * Set type of the parent document
	 *
	 * @param string $type Parent document type
	 * @return Elastica_Query_TopChildren Current object
	 */
	public function setType($type) {
		return $this->setParam('type', $type);
	}
}
