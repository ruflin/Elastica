<?php
/**
 * Term query
 *
 * @uses Elastica_Query_Abstract
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @link http://www.elasticsearch.org/guide/reference/query_dsl/ids-query.html
 */
class Elastica_Query_Ids extends Elastica_Query_Abstract
{
	protected $_ids = array();
	protected $_type = array();

	/**
	 * Constructs the Ids query object
	 *
	 * @param array $term OPTIONAL Calls setIds with the given $ids array
	 */
	public function __construct(array $ids = array()) {
		$this->setIds($ids);
	}

	/**
	 * Set ids can be used instead of addIds if some more special
	 * values for an ID have to be set.
	 *
	 * @param array|string $id Id array|string
	 * @return Elastica_Query_Ids Current object
	 */
	public function setIds($id) {
		if (is_array($id)) {
			$this->_ids = $id;
		} else if (is_numeric($id)) {
			// Includes IDs of 0
			$this->_ids = array($id);
		} else if (!empty($id)) {
			// Excludes empty strings, but not 0
			$this->_ids = array($id);
		}

		return $this;
	}

	/**
	 * Sets an optional type to search for IDs in
	 *
	 * @param array|string $type Type array|string
	 */
	public function setType($type) {
		if (is_array($type)) {
			$this->_type = $type;
		} else if (is_numeric($type)) {
			// Includes Types of 0
			$this->_type = array($type);
		} else if (!empty($type)) {
			// Excludes empty strings, but not 0
			$this->_type = array($type);
		}
	}

	/**
	 * Adds an id to the ids query
	 *
	 * @param string|array $value Values(s) for the query. Boost can be set with array
	 * @return Elastica_Query_Term Current object
	 */
	public function addId($id) {
		if (is_array($id)) {
			$this->_ids = array_merge($this->_ids, $id);
		} else {
			$this->_ids[] = $id;
		}

		return $this;
	}

	/**
	 * Converts the ids query to an array
	 *
	 * @return array Array ids query
	 */
	public function toArray() {
		$ids = array_unique($this->_ids);
		$type = array_unique($this->_type);

		$result = array('ids' => array());

		if (!empty($type)) {
			$result['ids']['type'] = $type;
		}

		if (!empty($ids)) {
			$result['ids']['values'] = $ids;
		}

		return $result;
	}
}
