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

	public function __construct(Elastica_Index $index) {
		$this->_index = $index;
		$this->refresh();
	}

	public function getData() {
		return $this->_data;
	}

	public function getName() {
		return $this->getIndex()->getName();
	}

	public function getAliases() {
		$data = $this->getData();
		return $data['indices'][$this->getName()]['aliases'];
	}

	public function hasAlias($name) {
		return in_array($name, $this->getAliases());
	}

	/**
	 * Returns the index object
	 *
	 * @return Elastica_Index Index object
	 */
	public function getIndex() {
		return $this->_index;
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
		$this->_response = $this->getIndex()->getClient()->request($path, Elastica_Request::GET);
		$this->_data = $this->getResponse()->getData();
	}
}
