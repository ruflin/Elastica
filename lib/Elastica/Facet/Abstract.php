<?php
/**
 * Abstract facet object. Should be extended by all facet types
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
abstract class Elastica_Facet_Abstract
{
	protected $_global = false;
	protected $_name = '';
	protected $_query = array();

	public function __construct($name) {
		$this->setName($name);
	}

	public function setName($name) {
		if (empty($name)) {
			throw new Elastica_Exception_Invalid('Facet name has to be set');
		}
		$this->_name = $name;
		return $this;
	}

	public function getName() {
		return $this->_name;
	}

	public function setFilter(Elastica_Filter_Abstract $filter) {
		return $this->setParam('facet_filter', $filter->toArray());
	}

	public function setParam($key, $value) {
		$this->_query[$key] = $value;
		return $this;
	}

	public function setGlobal($global = true) {
		$this->_query['global'] = (bool) $global;
	}

	abstract public function toArray();
}
