<?php
/**
 * Elastica index status object
 *
 * @link http://www.elasticsearch.org/guide/reference/api/admin-indices-status.html
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class Elastica_Index_Status
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

	/**
	 * Returns the entry in the data array based on the params.
	 * Various params possible.
	 *
	 * @return mixed Data array entry or null if not found
	 */
	public function get() {

		$data = $this->getData();
		$data = $data['indices'][$this->getName()];

		foreach (func_get_args() as $arg) {
			if (isset($data[$arg])) {
				$data = $data[$arg];
			} else {
				return null;
			}
		}

		return $data;
	}

	public function getAliases() {
		// TODO Update as soon as new API is implmented
		$cluster = new Elastica_Cluster($this->_index->getClient());
		$state = $cluster->getState();
		return $state['metadata']['indices'][$this->_index->getName()]['aliases'];
	}

	public function getSettings() {
		// TODO Update as soon as new API is implmented
		$cluster = new Elastica_Cluster($this->_index->getClient());
		$state = $cluster->getState();
		return $state['metadata']['indices'][$this->_index->getName()]['settings'];
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
	 * Reloads all status data of this object
	 */
	public function refresh() {
		$path = '_status';
		$this->_response = $this->getIndex()->request($path, Elastica_Request::GET);
		$this->_data = $this->getResponse()->getData();
	}
}
