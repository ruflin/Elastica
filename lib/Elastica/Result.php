<?php
/**
 * Elastica result item
 * 
 * Stores all information from a result
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class Elastica_Result
{
	protected $_hit;
	
	public function __construct(array $hit) {
		$this->_hit = $hit;
	}

	public function getId() {
		return $this->_hit['_id'];
	}
	
	/**
	 * Returns results type
	 * 
	 * @return string Result type
	 */
	public function getType() {
		return $this->_hit['_type'];
	}
	
	public function getIndex() {
		return $this->_hit['_index'];
	}
	
	public function getScore() {
		return $this->_hit['_score'];		
	}
	
	/**
	 * Returns result data
	 * 
	 * @return array Result data array
	 */
	public function getData() {
		$hit = $this->_hit;
		
		if (isset($this->_hit['_source'])) {
			return $this->_hit['_source'];
		} else {
			return array();
		}
	}
	
	public function __get($key) {
		$source = $this->getData();
		return array_key_exists($key, $source) ? $source[$key] : null; 
	}
}