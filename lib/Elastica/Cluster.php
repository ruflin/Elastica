<?php
/**
 * Cluster informations for elasticsearch
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @link http://www.elasticsearch.com/docs/elasticsearch/rest_api/admin/cluster
 */
class Elastica_Cluster
{
	protected $_client = null;

	/**
	 * Creates a cluster object
	 *
	 * @param Elastica_Client $client Connection client object
	 */
	public function __construct(Elastica_Client $client) {
		$this->_client = $client;
		$this->refresh();
	}

	/**
	 * Refreshs all cluster information (state)
	 */
	public function refresh() {
		$path = '_cluster/state';
		$this->_response = $this->_client->request($path, Elastica_Request::GET);
		$this->_data = $this->getResponse()->getData();
	}

	/**
	 * Returns the response object
	 *
	 * @return Elastica_Response Response object
	 */
	public function getResponse() {
		return $this->_response;
	}

	/**
	 * Returns the full state of the cluster
	 *
	 * @return array State array
	 * @link http://www.elasticsearch.org/guide/reference/api/admin-cluster-state.html
	 */
	public function getState() {
		return $this->_data;
	}

	/**
	 * Returns a list of existing node names
	 *
	 * @return array List of node names
	 */
	public function getNodeNames() {
		$data = $this->getState();
		return array_keys($data['routing_nodes']['nodes']);
	}

	/**
	 * Returns all nodes of the cluster
	 *
	 * @return array List of Elastica_Node objects
	 */
	public function getNodes() {
		$nodes = array();
		foreach ($this->getNodeNames() as $name) {
			$nodes[] = new Elastica_Node($name, $this->getClient());
		}
		return $nodes;
	}

	/**
	 * Returns the client object
	 *
	 * @return Elastica_Client Client object
	 */
	public function getClient() {
		return $this->_client;
	}

	/**
	 * Returns the cluster information (not implemented yet)
	 *
	 * @param array $args Additional arguemtns
	 * @link http://www.elasticsearch.com/docs/elasticsearch/rest_api/admin/cluster/nodes_info/
	 */
	public function getInfo(array $args) {
		throw new Exception('not implemented yet');
	}

	/**
	 * @param array $args OPTIONAL
	 * @link http://www.elasticsearch.com/docs/elasticsearch/rest_api/admin/cluster/health/
	 */
	public function getHealth($args = array()) {
		throw new Exception('not implemented yet');
	}

	/**
	 * Shuts down the complete cluster
	 *
	 * @param string $delay OPTIONAL Seconds to shutdown cluster after (default = 1s)
	 * @return Elastica_Response
	 * @link http://www.elasticsearch.org/guide/reference/api/admin-cluster-nodes-shutdown.html
	 */
	public function shutdown($delay = '1s') {
		$path = '_shutdown?delay=' . $delay;
		return $this->_client->request($path, Elastica_Request::POST);
	}
}
