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

	/**
	 * Returns status data
	 *
	 * @return array Status data
	 */
	public function getData() {
		return $this->_data;
	}

	/**
	 * Returns status objects of all indices
	 *
	 * @return array List of Elastica_Client_Index objects
	 */
	public function getIndexStatuses() {
		$statuses = array();
		foreach ($this->getIndexNames() as $name) {
			$index = new Elastica_Index($this->_client, $name);
			$statuses[] = new Elastica_Status_Index($index);
		}
		return $statuses;
	}

	/**
	 * Returns a list of the existing index names
	 *
	 * @return array Index names list
	 */
	public function getIndexNames() {
		$names = array();
		foreach($this->_data['indices'] as $name => $data) {
			$names[] = $name;
		}
		return $names;
	}

	/**
	 * Checks if the given index exists
	 *
	 * @param string $name Index name to check
	 * @return bool True if index exists
	 */
	public function indexExists($name) {
		return in_array($name, $this->getIndexNames());
	}

	/**
	 * Checks if the given alias exists
	 *
	 * @param string $name Alias name
	 * @return bool True if alias exists
	 */
	public function aliasExists($name) {
		foreach ($this->getIndexStatuses() as $status) {
			if ($status->hasAlias($name)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Returns an array with all indices that the given alias name points to
	 *
	 * @return array List of Elastica_Index
	 */
	public function getIndicesWithAlias($name) {
		$indices = array();
		foreach ($this->getIndexStatuses() as $status) {
			if ($status->hasAlias($name)) {
				$indices[] = $status->getIndex();
			}
		}
		return $indices;
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
