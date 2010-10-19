<?php

// Stores query time, and result array -> is given to resultset, returned by ...

class Elastica_Request {
	
	const POST = 'POST';
	const PUT = 'PUT';
	const GET = 'GET';
	const DELETE = 'DELETE';
	
	protected $_path;
	// TODO: set default method?
	protected $_method;
	protected $_data;
	
	public function __construct($path, $method, $data = array()) {
		$this->_path = $path;
		$this->_method = $method;
		$this->_data = $data;
	}
	
	public function setMethod($method) {
		$this->_method = $method;
	}
	
	public function getMethod() {
		return $this->_method;
	}
	
	public function setData($data) {
		$this->_data = $data;
	}
	
	public function getData() {
		return $this->_data;
	}
	
	public function setPath($path) {
		$this->_path = $path;
	}
	
	public function getPath() {
		return $this->_path;
	}
}