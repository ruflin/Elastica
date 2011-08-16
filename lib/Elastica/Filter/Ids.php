<?php
/**
 * Ids Filter
 *
 * @uses Elastica_Filter_Abstract
 * @category Xodoa
 * @package Elastica
 * @author Lee Parker, Nicolas Ruflin <spam@ruflin.com>
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/ids-filter.html
 */
class Elastica_Filter_Ids extends Elastica_Filter_Abstract
{
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
	 * @return Elastica_Filter_Ids Current object
	 */
	public function addId($id) {
		return $this->addParam('values', $id);
	}

	/**
	 * Adds one more type to query
	 *
	 * @param string $type Adds type to query
	 * @return Elastica_Filter_Ids Current object
	 */
	public function addType($type) {
		if ($type instanceof Elastica_Type) {
			$type = $type->getType();
		} else if (empty($type) && !is_numeric($type)) {
			// TODO: Shouldn't this throw an exception?
			// A type can be 0, but cannot be empty
			return $this;
		}

		return $this->addParam('type', $type);
	}

	/**
	 * @param string|Elastica_Type $type Type name or object
	 * @return Elastica_Filter_Ids Current object
	 */
	public function setType($type) {
		if ($type instanceof Elastica_Type) {
			$type = $type->getType();
		} else if (empty($type) && !is_numeric($type)) {
			// TODO: Shouldn't this throw an exception or let handling of invalid params to ES?
			// A type can be 0, but cannot be empty
			return $this;
		}

		return  $this->setParam('type', $type);
	}

	/**
	 * Sets the ids to filter
	 *
	 * @param array|string $ids List of ids
	 * @return Elastica_Filter_Ids Current object
	 */
	public function setIds($ids) {
		if (!is_array($ids)) {
			$ids = array($ids);
		}

		return $this->setParam('values', $ids);
	}
}
