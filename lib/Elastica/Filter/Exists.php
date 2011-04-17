<?php
/**
 * Exists query
 *
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/exists-filter.html
 * @uses Elastica_Query_Abstract
 * @category Xodoa
 * @package Elastica
 * @author Oleg Cherniy <oleg.cherniy@gmail.com>
 */
class Elastica_Filter_Exists extends Elastica_Filter_Abstract
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

	public function toArray() {
		$args = array('field' => $this->_field);

		return array('exists' => $args);
	}
}
