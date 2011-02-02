<?php
/**
 * Bool Filter
 *
 * @link http://www.elasticsearch.com/docs/elasticsearch/rest_api/query_dsl/bool_query/
 * @uses Elastica_Filter_Abstract
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class Elastica_Filter_Bool extends Elastica_Filter_Abstract
{
	protected $_minimumNumberShouldMatch = 1;

	protected $_must = array();
	protected $_should = array();
	protected $_mustNot = array();

	public function addShould($args) {
		$this->_addFilter('should', $args);
	}

	public function addMust($args) {
		$this->_addFilter('must', $args);
	}

	public function addMustNot($args) {
		$this->_addFilter('mustNot', $args);
	}

	public function _addFilter($type, $args) {
		if ($args instanceof Elastica_Filter_Abstract) {
			$args = $args->toArray();
		}

		if (!is_array($args)) {
			throw new Elastica_Exception_Invalid('Invalid parameter. Has to be array or instance of Elastica_Filter');
		}

		$varName = '_' . $type;
		$this->{$varName}[] = $args;
	}

	public function toArray() {
		$args = array();

		if (!empty($this->_must)) {
			$args['must'] = $this->_must;
		}

		if (!empty($this->_should)) {
			$args['should'] = $this->_should;
			$args['minimum_number_should_match'] = $this->_minimumNumberShouldMatch;
		}

		if (!empty($this->_mustNot)) {
			$args['must_not'] = $this->_mustNot;
		}

		return array('bool' => $args);
	}

	public function setBoost($boost) {
		$this->_boost = $boost;
	}

	public function setMinimumNumberShouldMatch($minimumNumberShouldMatch) {
		$this->_minimumNumberShouldMatch = intval($minimumNumberShouldMatch);
	}

}
