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
	}

	abstract function toArray();
}
