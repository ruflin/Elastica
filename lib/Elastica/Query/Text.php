<?php
/**
 * Text query
 *
 * @uses Elastica_Query_Abstract
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/text-query.html
 */
class Elastica_Query_Text extends Elastica_Query_Abstract
{
	protected $_message = null;

	/**
	 * @param string $query Query string
	 */
	public function __construct($query) {
		$this->_message = new Elastica_Param();
		$this->setQuery($query);
	}

	/**
	 * Sets a param for the message array
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return Elastica_Query_Text
	 */
	public function setMessageParam($key, $value) {
		$this->_message->setParam($key, $value);
		return $this;
	}

	/**
	 * Sets the query string
	 *
	 * @param string $query
	 * @return Elastica_Query_Text
	 */
	public function setQuery($query) {
		return $this->setMessageParam('query', $query);
	}

	/**
	 * @param string $type Text query type
	 * @return Elastica_Query_Text
	 */
	public function setType($type) {
		return $this->setMessageParam('type', $type);
	}

	/**
	 * @param int $maxExpansions
	 * @return Elastica_Query_Text
	 */
	public function setMaxExpansions($maxExpansions) {
		return $this->setMessageParam('max_expansions', $maxExpansions);
	}

	/**
	 * @see Elastica_Param::toArray()
	 */
	public function toArray() {
		$this->setParams(array('message' => $this->_message->getParams()));
		return parent::toArray();
	}
}
