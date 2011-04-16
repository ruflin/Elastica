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
	 * @param string $field
	 * @return Elastica_Facet_Range
	 */
	public function setField($field) {
		/**
		 * Unset the fields by their separates.
		 */
		$this->unsetParam('key_field')
		     ->unsetParam('value_field');
		
		/**
		 * Set the field name.
		 */
		return $this->setParam('field', $field);
	}
	
	/**
	 * Sets the fields by their separate key and value fields.
	 * 
	 * @param string $key_field
	 * @param string $value_field
	 * @return Elastica_Facet_Range
	 */
	public function setKeyValueField($key_field, $value_field) {
		/**
		 * Only one of the types can be defined, reset the others.
		 */
		$this->unsetParam('field');
		
		/**
		 * Set the key field and value field.
		 */
		return $this->setParam('key_field', $key_field)
		            ->setParam('value_field', $value_field);
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
	 * @param mixed $from
	 * @param mixed $to
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
	 * @param $key
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
	 * Unsets a parameter for this facet if it is set.
	 * 
	 * @param string $key
	 * @return Elastica_Facet_Range
	 */
	public function unsetParam($key) {
		if (isset($this->_params[$key])) {
			unset($this->_params[$key]);
		}
		return $this;
	}
	
	/**
	 * Creates the full facet definition, which includes the basic
	 * facet definition of the parent.
	 * 
	 * @see Elastica_Facet_Abstract::toArray()
	 * @return array
	 */
	public function toArray() {
		parent::setParam('range', $this->_params);
		return $this->_query;
	}
}
