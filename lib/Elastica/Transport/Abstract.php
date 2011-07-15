<?php
/**
 * Elastica Abstract Transport object
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
abstract class Elastica_Transport_Abstract {

	protected $_path;
	// TODO: set default method?
	protected $_method;
	protected $_data;
	protected $_config;


	/**
	 * @param Elastica_Request $request Request object
	 */
	public function __construct(Elastica_Request $request) {
		$this->_request = $request;
	}

	/**
	 * Returns the request object
	 *
	 * @return Elastica_Request Request object
	 */
	public function getRequest() {
		return $this->_request;
	}

	/**
	 * Executes the transport request
	 *
	 * @param string $host Hostname
	 * @param int $port Port number
	 * @return Elastica_Response Response object
	 */
	abstract public function exec($host, $port);
}