<?php
/**
 * Elastica general status
 *
 * @link http://www.elasticsearch.org/guide/reference/api/admin-indices-status.html
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class Elastica_Status
{

	/**
	 * Contains all status infos
	 *
	 * @var Elastica_Response Response object
	 */
	protected $_response = null;

	protected $_data = array();

	protected $_client = null;

	public function __construct(Elastica_Client $client) {
		$this->_client = $client;
		$this->refresh();
	}

	public function getData() {
		return $this->_data;
	}

	public function getIndexStatuses() {
		$statuses = array();
		foreach ($this->getIndexNames() as $name) {
			$statuses[] = new Elastica_Status_Index($name, $this->_client);
		}
		return $statuses;
	}

	public function getIndexNames() {
		$names = array();
		foreach($this->_data['indices'] as $name => $data) {
			$names[] = $name;
		}
		return $names;
	}

	/**
	 * Returns response object
	 *
	 * @return Elastica_Response Response object
	 */
	public function getResponse() {
		return $this->_response;
	}

	public function getShards() {
		return $this->_data['shards'];
	}

	public function refresh() {
		$path = '_status';
		$this->_response = $this->_client->request($path, Elastica_Request::GET);
		$this->_data = $this->getResponse()->getData();
	}
}
