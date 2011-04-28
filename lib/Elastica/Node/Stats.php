<?php
/**
 * Elastica cluster node object
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @link http://www.elasticsearch.org/guide/reference/api/admin-indices-status.html
 */
class Elastica_Node_Stats
{
	protected $_response = null;

	protected $_data = array();

	protected $_node = null;

	/**
	 * @param Elastica_Node $node Elastica node object
	 */
	public function __construct(Elastica_Node $node) {
		$this->_node = $node;
		$this->refresh();
	}

	/**
	 * Returns all node stats as array based on the arguments
	 *
	 * Several arguments can be use
	 * get('index', 'test', 'example')
	 *
	 * @return array Node stats for the given field or null if not found
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
	 * Returns all stats data
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
		$path = '_cluster/nodes/' . $this->getNode()->getName() . '/stats';
		$this->_response = $this->getNode()->getClient()->request($path, Elastica_Request::GET);
		$data = $this->getResponse()->getData();
		$this->_data = reset($data['nodes']);
	}
}
