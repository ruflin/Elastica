<?php
/**
 * Implements the range facet.
 *
 * @category Xodoa
 * @package Elastica
 * @author Jasper van Wanrooy <jasper@vanwanrooy.net>
 * @link http://www.elasticsearch.org/guide/reference/api/search/facets/range-facet.html
 * @todo Implement the script based key and value.
 */
class Elastica_Facet_Range extends Elastica_Facet_Abstract
{
	/**
	 * Holds the parameters of the range facet.
	 * 
	 * @var array
	 */
	protected $_params = array();
	
	/**
	 * Sets the field for the range.
	 * 
	 * @param string $field The name of the field for range.
	 * @return Elastica_Facet_Range
	 */
	public function setField($field) {
		return $this->setParam('field', $field);
	}
	
	/**
	 * Sets the fields by their separate key and value fields.
	 * 
	 * @param string $keyField The key_field param for the range. 
	 * @param string $valueField The key_value param for the range.
	 * @return Elastica_Facet_Range
	 */
	public function setKeyValueFields($keyField, $valueField) {
		return $this->setParam('key_field', $keyField)
					->setParam('value_field', $valueField);
	}
	
	/**
	 * Sets the ranges for the facet all at once. Sample ranges: 
	 * array (
	 *     array('to' => 50),
	 *     array('from' => 20, 'to' 70),
	 *     array('from' => 70, 'to' => 120),
	 *     array('from' => 150)
	 * )
	 * 
	 * @param array $ranges Numerical array with range definitions.
	 * @return Elastica_Facet_Range
	 */
	public function setRanges(array $ranges) {
		return $this->setParam('ranges', $ranges);
	}
	
	/**
	 * Adds a range to the range facet.
	 * 
	 * @param mixed $from The from for the range.
	 * @param mixed $to The to for the range.
	 * @return Elastica_Facet_Range
	 */
	public function addRange($from = null, $to = null) {
		if (!isset($this->_params['ranges']) || !is_array($this->_params['ranges'])) {
			$this->_params['ranges'] = array();
		}
		
		$this->_params['ranges'][] = array('from' => $from, 'to' => $to);
		return $this;
	}
	
	/**
	 * Gets a general parameter for this facet by key.
	 * 
	 * @param string $key The key of the param to fetch.
	 * @return mixed
	 */
	public function getParam($key) {
		if (isset($this->_params[$key])) {
			return $this->_params[$key];
		}
	}

	/**
	 * Sets a general parameter for this facet by key and value.
	 *
	 * @param string $key Key to set
	 * @param mixed $value Value
	 * @return Elastica_Facet_Range
	 */
	public function setParam($key, $value) {
		$this->_params[$key] = $value;
		return $this;
	}
	
	/**
	 * Creates the full facet definition, which includes the basic
	 * facet definition of the parent.
	 * 
	 * @see Elastica_Facet_Abstract::toArray()
	 * @throws Elastica_Exception_Invalid When the right fields haven't been set.
	 * @return array
	 */
	public function toArray() {
		/**
		 * Check the facet for validity:
		 *  - only the field param OR the key_field should be set.
		 */
		if (!isset($this->_params['field']) && !isset($this->_params['key_field'])) {
			throw new Elastica_Exception_Invalid('Neither field nor key_field is set.');
		}
		
		if (isset($this->_params['field']) && $this->_params['key_field']) {
			throw new Elastica_Exception_Invalid('Either field or key_field and key_value should be set.');
		}
		
		/**
		 * Set the range in the abstract as param.
		 */
		parent::setParam('range', $this->_params);
		return $this->_query;
	}
}
