<?php
/**
 * Prefix filter
 *
 * @uses Elastica_Query_Abstract
 * @category Xodoa
 * @package Elastica
 * @author Jasper van Wanrooy <jasper@vanwanrooy.net>
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/prefix-filter.html
 */
class Elastica_Filter_Prefix extends Elastica_Filter_Abstract
{
	/**
	 * Holds the name of the field for the prefix.
	 * 
	 * @var string
	 */
	protected $_field = '';
	
	/**
	 * Holds the prefix string.
	 * 
	 * @var string
	 */
	protected $_prefix = '';

	/**
	 * Creates prefix filter
	 *
	 * @param string $field Field name
	 * @param string $prefix Prefix string
	 */
	public function __construct($field = '', $prefix = '') {
		$this->setField($field);
		$this->setPrefix($prefix);
	}

	/**
	 * Sets the name of the prefix field.
	 *
	 * @param string $field Field name
	 */
	public function setField($field) {
		$this->_field = $field;
		return $this;
	}
	
	/**
	 * Sets the prefix string.
	 *
	 * @param string $prefix Prefix string
	 */
	public function setPrefix($prefix) {
		$this->_prefix = $prefix;
		return $this;
	}

	/**
	 * Convers object to an arrray
	 *
	 * @see Elastica_Filter_Abstract::toArray()
	 * @return array data array
	 */
	public function toArray() {
		$this->setParam($this->_field, $this->_prefix);
		return parent::toArray();
	}
}
