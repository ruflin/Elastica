<?php
/**
 * Terms facet
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class Elastica_Facet_Terms extends Elastica_Facet_Abstract
{
	protected $_params = array();

	public function setField($field) {
		return $this->setParam('field', $field);
	}

	public function setFields(array $fields) {
		return $this->setParam('field', $fields);
	}

	public function setAllTerms($allTerms) {
		$allTerms = (bool) $allTerms;
		return $this->setParam('all_terms', $allTerms);
	}

	public function setExclude(array $exclude) {
		return $this->setParam('exclude', $exclude);
	}

	/**
	 * Sets a general parameter for this Facet by key and value
	 *
	 * @param string $key Key to set
	 * @param mixed $value Value
	 */
	public function setParam($key, $value) {
		$this->_params[$key] = $value;
		return $this;
	}

	public function setSize($size) {
		$size = (int) $size;
		return $this->setParam('size', $size);
	}

	public function toArray() {
		parent::setParam('terms', $this->_params);
		return $this->_query;
	}
}
