<?php
/**
 * Elastica Request object
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class Elastica_Request {

	const POST = 'POST';
	const PUT = 'PUT';
	const GET = 'GET';
	const DELETE = 'DELETE';

	protected $_path;
	// TODO: set default method?
	protected $_method;
	protected $_data;

	/**
	 * @param string $path Request path
	 * @param string $method Request method (use const's)
	 * @param array $data Data array
	 */
	public function __construct($path, $method, $data = array()) {
		$this->_path = $path;
		$this->_method = $method;
		$this->_data = $data;
	}

	/**
	 * Sets the request method. Use one of the for consts
	 *
	 * @param string $method Request method
	 * @return Elastica_Request Current object
	 */
	public function setMethod($method) {
		$this->_method = $method;
		return $this;
	}

	/**
	 * @return string Request method
	 */
	public function getMethod() {
		return $this->_method;
	}

	/**
	 * Sets the request data
	 *
	 * @param array $data Request data
	 */
	public function setData($data) {
		$this->_data = $data;
		return $this;
	}

	/**
	 * @return array Request data
	 */
	public function getData() {
		return $this->_data;
	}

	/**
	 * Sets the request path
	 *
	 * @param string $path Request path
	 * @return Elastica_Request Current object
	 */
	public function setPath($path) {
		$this->_path = $path;
		return $this;
	}

	/**
	 * @return string Request path
	 */
	public function getPath() {
		return $this->_path;
	}
}