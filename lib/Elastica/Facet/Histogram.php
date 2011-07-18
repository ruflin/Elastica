<?php
/**
 * Implements the Histogram facet.
 *
 * @category Xodoa
 * @package Elastica
 * @author Raul Martinez Jr  <juneym@gmail.com>
 * @link http://www.elasticsearch.org/guide/reference/api/search/facets/histogram-facet.html
 */
class Elastica_Facet_Histogram extends Elastica_Facet_Abstract
{
	/**
	 * Holds the parameters of the range facet.
	 *
	 * @var array
	 */
	protected $_params = array();

	/**
	 * Sets the field for histogram
	 *
	 * @param string $field The name of the field for the historgram
	 * @return Elastica_Facet_Histogram
	 */
	public function setField($field) {
		return $this->setParam('field', $field);
	}

	/**
	 * Set the value for interval
	 *
	 * @param string $interval
	 * @return Elastica_Facet_Range
	 */
	public function setInterval($interval) {
		return $this->setParam('interval', $interval);
	}


	/**
	 * Set the fields for key_field and value_field
	 *
	 * @param string $keyField Key field
	 * @param string $valueField Value field
	 * @return Elastica_Facet_Range
	 */
	public function setKeyValueFields($keyField, $valueField) {
		return $this->setParam('key_field', $keyField)->setParam('value_field', $valueField);
	}

	/**
	 * Sets the key and value for this facet by script.
	 *
	 * @param string $keyScript Script to check whether it falls into the range.
	 * @param string $valueScript Script to use for statistical calculations.
	 */
	public function setKeyValueScripts($keyScript, $valueScript) {
		return $this->setParam('key_script', $keyScript)
					->setParam('value_script', $valueScript);
	}

	/**
	 * Set the "params" essential to the a script
	 *
	 * @param array $params Associative array (key/value pair)
	 * @return Elastica_Facet_Histogram Facet object
	 */
	public function setScriptParams(Array $params) {
		return $this->setParam('params', $params);
	}


	/**
	 * Gets a general parameter for this facet by key.
	 *
	 * @param string $key The key of the param to fetch.
	 * @return mixed Key value or null
	 */
	public function getParam($key) {
		if (isset($this->_params[$key])) {
			return $this->_params[$key];
		}
		// TODO: check if should throw exception
		return null;
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
		 * Set the range in the abstract as param.
		 */
		$this->_setFacetParam('histogram', $this->_params);
		return parent::toArray();
	}
}