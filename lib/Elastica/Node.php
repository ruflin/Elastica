<?php
/**
 * Elastica cluster node object
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @link http://www.elasticsearch.org/guide/reference/api/admin-indices-status.html
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

	/**
	 * @return string Node name
	 */
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

	/**
	 * @return Elastica_Node_Info Node info object
	 */
	public function getInfo() {
		if (!$this->_info) {
			$this->_info = new Elastica_Node_Info($this);
		}

		return $this->_info;
	}

	/**
	 * Refreshs all node information
	 *
	 * This should be called after upating a node to refresh all information
	 */
	public function refresh() {
		$this->_stats = null;
		$this->_info = null;
	}

	/**
	 * Shuts this node down
	 *
	 * @param string $delay OPTIONAL Delay after which node is shut down (defualt = 1s)
	 * @return Elastica_Response
	 * @link http://www.elasticsearch.org/guide/reference/api/admin-cluster-nodes-shutdown.html
	 */
	public function shutdown($delay = '1s') {
		$path = '_cluster/nodes/' . $this->getName() . '/_shutdown?delay=' . $delay;
		return $this->_client->request($path, Elastica_Request::POST);
	}
}
