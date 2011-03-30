<?php
/**
 * Terms facet
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class Elastica_Facets
{
	protected $_facets = array();

	public function __construct() {

	}

	public function setGlobal($global = true) {
		$this->_global = $global;
	}

	public function setScope($scope) {
		$this->_scope = $scope;
	}

	public function addFacet(Elastica_Facet_Abstract $facet) {
		$this->_facets[] = $facet;
	}

	public function setFilter(Elastica_Filter_Abstract $filter) {
	}

	public function set(array $facets) {
		$this->_facets = $facets;
	}

	public function toArray() {
		$params = array();

		foreach ($this->_facets as $facet) {
			$params[$facet->getName()] = $facet->toArray();
		}

		return array('facets' => $params);
	}
}
