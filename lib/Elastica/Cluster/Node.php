<?php
/**
 * Elastica cluster node object
 *
 * @link http://www.elasticsearch.org/guide/reference/api/admin-indices-status.html
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class Elastica_Cluster_Node
{
	protected $_response = null;

	protected $_data = array();

	protected $_name = '';

	public function __construct($name, Elastica_Client $client) {
		$this->_name = $name;
		$this->_client = $client;
		$this->refresh();
	}

	public function getData() {
		return $this->_data;
	}

	public function getName() {
		return $this->_name;
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
	 * Loads status
	 *
	 * @return Elastica_Response Response object
	 */
	public function _request($path) {
		$path = '_cluster/';
		$this->_response = $this->_client->request($path, Elastica_Request::GET);
		$this->_data = $this->getResponse()->getData();
	}
}
