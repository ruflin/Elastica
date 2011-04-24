<?php
/**
 * Wildcard query
 *
 * @uses Elastica_Query_Abstract
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @link http://www.elasticsearch.com/docs/elasticsearch/rest_api/query_dsl/wildcard_query
 */
class Elastica_Query_Wildcard extends Elastica_Query_Abstract
{
	protected $_key;
	protected $_value;
	protected $_boost;

	/**
	 * @param string $key Wildcard key
	 * @param string $value Wildcard value
	 * @param float $boost OPTIONAL Boost value (default = 1)
	 */
	public function __construct($key, $value, $boost = 1) {
		$this->_key = $key;
		$this->_value = $value;
		$this->_boost = $boost;
	}


	/**
	 * Returns query in form of an array
	 *
	 * @return array Query as array
	 */
	public function toArray() {
		return array('wildcard' => array(
			$this->_key => array(
				'value' => $this->_value,
				'boost' => $this->_boost)
			)
		);
	}
}
