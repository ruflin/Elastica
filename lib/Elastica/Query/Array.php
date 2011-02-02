<?php
/**
 * Array query
 * Pure php array query. Can be used to create any not existing type of query.
 *
 * @uses Elastica_Query_Abstract
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class Elastica_Query_Array extends Elastica_Query_Abstract
{
	protected $_query = array();
	
	public function __construct(array $query) {
		$this->setQuery($query);
	}
	
	public function setQuery(array $query) {
		$this->_query = $query;
	}
	
	public function toArray() {
		return $this->_query;
	}
}
