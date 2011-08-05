<?php
/**
 * Elastica cluster node object
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @link http://www.elasticsearch.org/guide/reference/api/admin-indices-status.html
 */
class Elastica_Node_Info
{
	protected $_response = null;

	protected $_data = array();

	protected $_node = null;

	/**
	 * @param Elastica_Node $node Node object
	 */
	public function __construct(Elastica_Node $node) {
		$this->_node = $node;
		$this->refresh();
	}

	/**
	 * Returns the entry in the data array based on the params.
	 * Several params possible.
	 *
	 * Example 1: get('os', 'mem', 'total') returns total memory of the system the
	 * node is running on
	 * Example 2: get('os', 'mem') returns an array with all mem infos
	 *
	 * @return mixed Data array entry or null if not found
	 */
	public function get() {

		$data = $this->getData();

		foreach (func_get_args() as $arg) {
			if (isset($data[$arg])) {
				$data = $data[$arg];
			} else {
				return null;
			}
		}

		return $data;
	}

	/**
	 * @return string Returns Node port
	 */
	public function getPort() {
		// Returns string in format: inet[/192.168.1.115:9201]
		$data = $this->get('http_address');
		$data = substr($data, 6, strlen($data) - 7);
		$data = explode(':', $data);

		return $data[1];
	}

	/**
	 * @return string Returns Node ip address
	 */
	public function getIp() {
		// Returns string in format: inet[/192.168.1.115:9201]
		$data = $this->get('http_address');
		$data = substr($data, 6, strlen($data) - 7);
		$data = explode(':', $data);

		return $data[0];
	}

	/**
	 * Return all info data
	 *
	 * @return array Data array
	 */
	public function getData() {
		return $this->_data;
	}

	/**
	 * @return Elastica_Node Node object
	 */
	public function getNode() {
		return $this->_node;
	}

	/**
	 * Returns response object
	 *
	 * @return Elastica_Response Response object
	 */
	public function getResponse() {
		return $this->_response;
	}

	/**
	 * Reloads all nodes information. Has to be called if informations changed
	 *
	 * @return Elastica_Response Response object
	 */
	public function refresh() {
		$path = '_cluster/nodes/' . $this->getNode()->getName();
		$this->_response = $this->getNode()->getClient()->request($path, Elastica_Request::GET);
		$data = $this->getResponse()->getData();
		$this->_data = reset($data['nodes']);
	}
}
