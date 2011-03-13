<?php
/**
 * Elastica index status object
 *
 * @link http://www.elasticsearch.org/guide/reference/api/admin-indices-status.html
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class Elastica_Status_Index
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

	public function getAliases() {
		$data = $this->getData();
		return $data['indices'][$this->getName()]['aliases'];
	}

	public function hasAlias($name) {
		return in_array($name, $this->getAliases());
	}

	/**
	 * Returns the client object
	 *
	 * @return Elastica_Client Client object
	 */
	public function getIndex() {
		return new Elastica_Index($this->_client, $this->getName());
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
	 * Reloads all status data
	 *
	 * @return Elastica_Response Response object
	 */
	public function refresh() {
		$path = $this->getName()  . '/_status';
		$this->_response = $this->_client->request($path, Elastica_Request::GET);
		$this->_data = $this->getResponse()->getData();
	}
}
