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
	protected $_params = array();

	/**
	 * Creates filter object
	 *
	 * @param string|Elastica_Type $type Type to filter on
	 * @param array $ids List of ids
	 */
	public function __construct($type, array $ids = array()) {
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
		$this->_params['values'][] = $id;
		return $this;
	}

	/**
	 * @param string|Elastica_Type $type Type name or object
	 * @return Elastica_Filter_Ids Current object
	 */
	public function setType($type) {
		if ($type instanceof Elastica_Type) {
			$type = $type->getType();
		}

		$this->_params['type'] = $type;
		return $this;
	}

	/**
	 * Sets the ids to filter
	 *
	 * @param array $ids List of ids
	 * @return Elastica_Filter_Ids Current object
	 */
	public function setIds(array $ids) {
		$this->_params['values'] = $ids;
		return $this;
	}

	/**
	 * Converts filter to array
	 *
	 * @see Elastica_Filter_Abstract::toArray()
	 * @return array Filter array
	 */
	public function toArray() {
		return array('ids' => $this->_params);
	}
}
