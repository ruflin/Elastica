<?php
/**
 * Elastica cluster node object
 *
 * @link http://www.elasticsearch.org/guide/reference/api/admin-indices-status.html
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class Elastica_Node
{
	protected $_name = '';

	protected $_stats = null;

	protected $_info = null;

	/**
	 * Create a new node object
	 *
	 * @param string $name Node name
	 * @param Elastica_Client $client Node object
	 */
	public function __construct($name, Elastica_Client $client) {
		$this->_name = $name;
		$this->_client = $client;
		$this->refresh();
	}

	public function getName() {
		return $this->_name;
	}

	/**
	 * Returns the current client object
	 *
	 * @return Elastica_Client Client
	 */
	public function getClient() {
		return $this->_client;
	}

	/**
	 * Return stats object of the current node
	 *
	 * @return Elastica_Node_Stats Node stats
	 */
	public function getStats() {
		if (!$this->_stats) {
			$this->_stats = new Elastica_Node_Stats($this);
		}

		return $this->_stats;
	}

	public function getInfo() {
		if (!$this->_info) {
			$this->_info = new Elastica_Node_Info($this);
		}

		return $this->_info;
	}

	public function refresh() {
		$this->_stats = null;
		$this->_info = null;
	}
}
