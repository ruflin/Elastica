<?php
/**
 * Ids Query
 *
 * @uses Elastica_Query_Abstract
 * @category Xodoa
 * @package Elastica
 * @author Lee Parker
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @author Tim Rupp
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/ids-query.html
 */
class Elastica_Query_Ids extends Elastica_Query_Abstract
{
	protected $_params = array();

	/**
	 * Creates filter object
	 *
	 * @param string|Elastica_Type $type Type to filter on
	 * @param array $ids List of ids
	 */
	public function __construct($type = null, array $ids = array()) {
		$this->setType($type);
		$this->setIds($ids);
	}

	/**
	 * Adds one more filter to the and filter
	 *
	 * @param string $id Adds id to filter
	 * @return Elastica_Query_Ids Current object
	 */
	public function addId($id) {
		$this->_params['values'][] = $id;
		return $this;
	}

	/**
	 * Adds one more type to query
	 *
	 * @param string $type Adds type to query
	 * @return Elastica_Query_Ids Current object
	 */
	public function addType($type) {
		if ($type instanceof Elastica_Type) {
			$type = $type->getType();
		} else if (empty($type) && !is_numeric($type)) {
			// A type can be 0, but cannot be empty
			return $this;
		}

		$this->_params['type'][] = $type;
		return $this;
	}

	/**
	 * @param string|Elastica_Type $type Type name or object
	 * @return Elastica_Query_Ids Current object
	 */
	public function setType($type) {
		if ($type instanceof Elastica_Type) {
			$type = $type->getType();
		} else if (empty($type) && !is_numeric($type)) {
			// A type can be 0, but cannot be empty
			return $this;
		}

		$this->_params['type'] = $type;
		return $this;
	}

	/**
	 * Sets the ids to filter
	 *
	 * @param array|string $ids List of ids
	 * @return Elastica_Query_Ids Current object
	 */
	public function setIds($ids) {
		if (is_array($ids)) {
			$this->_params['values'] = $ids;
		} else {
			$this->_params['values'] = array($ids);
		}

		return $this;
	}

	/**
	 * Converts filter to array
	 *
	 * @see Elastica_Query_Abstract::toArray()
	 * @return array Query array
	 */
	public function toArray() {
		return array('ids' => $this->_params);
	}
}