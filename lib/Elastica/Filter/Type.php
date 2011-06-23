<?php
/**
 * Type query
 *
 * @uses Elastica_Query_Abstract
 * @category Xodoa
 * @package Elastica
 * @author Fredrik Wollsén <fredrik@neam.se>, Oleg Cherniy <oleg.cherniy@gmail.com>
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/exists-filter.html
 */
class Elastica_Filter_Type extends Elastica_Filter_Abstract
{
	/**
	 * @var string
	 */
	protected $_field;

	/**
	 * @param string $field
	 */
	public function __construct($field) {
		$this->setField($field);
	}

	/**
	 * @param string $field
	 */
	public function setField($field) {
		$this->_field = $field;
		return $this;
	}

	/**
	 * Converts filter to array
	 *
	 * @see Elastica_Filter_Abstract::toArray()
	 * @return array Filter array
	 */
	public function toArray() {
		$args = array('value' => $this->_field);

		return array('type' => $args);
	}
}
