<?php

/**
 * Returns parent documents having child docs matching the query
 *
 * @uses Elastica_Query_Abstract
 * @category Xodoa
 * @package Elastica
 * @author Fabian Vogler <fabian@equivalence.ch>
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/has-child-query.html
 */
class Elastica_Query_HasChild extends Elastica_Query_Abstract
{
	/**
	 * @var Elastica_Query
	 */
	protected $_query;

	protected $_type = null;

	/**
	 * @param string|Elastica_Query $query Query string or a Elastica_Query object
	 * @param string $type Parent document type
	 */
	public function __construct($query, $type = null) {
		$this->_query = Elastica_Query::create($query);
		$this->setType($type);
	}

	/**
	 * Set type of the parent document
	 *
	 * @param string $type Parent document type
	 * @return Elastica_Query_HasChild Current object
	 */
	public function setType($type) {
		$this->_type = $type;
		return $this;
	}

	/**
	 * Converts has_child query to array
	 *
	 * @return array Query array
	 * @see Elastica_Query_Abstract::toArray()
	 */
	public function toArray() {

		$args = $this->_query->toArray();

		if ($this->_type) {
			$args['type'] = $this->_type;
		}

		return array('has_child' => $args);
	}
}
