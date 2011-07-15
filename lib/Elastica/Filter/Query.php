<?php

/**
 * Query filter
 *
 * @uses Elastica_Filter_Query
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class Elastica_Filter_Query extends Elastica_Filter_Abstract {

	/**
	 * @var array|Elastica_Query_Abstract
	 */
	protected $_query = null;

	/**
	 * @param array|Elastica_Query_Abstract $query
	 */
	public function __construct($query = null) {
		if (!is_null($query)) {
			$this->setQuery($query);
		}
	}

	/**
	 * @param array|Elastica_Query_Abstract $query
	 * @return Elastca_Filter_Query Query object
	 * @throws Elastica_Exception_Invalid Invalid param
	 */
	public function setQuery($query) {
		if (!$query instanceof Elastica_Query_Abstract && ! is_array($query)) {
			throw new Elastica_Exception_Invalid('expected an array or instance of Elastica_Query_Abstract');
		}

		if ($query instanceof Elastica_Query_Abstract) {
			$this->_query = $query->toArray();
		} else {
			$this->_query = $query;
		}

		return $this;
	}

	/**
	 * @return array Query filter
	 * @see Elastica_Filter_Abstract::toArray()
	 */
	public function toArray() {
		return array(
			'query' => (
				$this->_query
			),
		);
	}
}
