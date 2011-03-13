<?php

// Stores query time, and result array -> is given to resultset, returned by ...

class Elastica_Response {

	protected $_queryTime = null;
	protected $_responseString = '';
	protected $_error = false;
	protected $_transferInfo = array();

	public function __construct($responseString) {
		$this->_responseString = $responseString;
	}

	public function getError() {
		$message = '';
		$response = $this->getData();

		if (isset($response['error'])) {
			$message = $response['error'];
		}
		return $message;
	}

	public function hasError() {
		$response = $this->getData();

		if (isset($response['error'])) {
			return $response['error'];
		}
		return false;
	}

	/**
	 * @deprecated Use getData
	 */
	public function getResponse() {
		return $this->getData();
	}

	public function getData() {

		$response = $this->_responseString;
		if ($response === false) {
			$this->_error = true;
		} else {

			$tempResponse = json_decode($response, true);
			// If error is returned, json_decod makes empty string of string
			if (!empty($tempResponse)) {
				$response = $tempResponse;
			}
		}

		if (empty($response)) {
			$response = array();
		}

		if (is_string($response)) {
			$response = array('message' => $response);
		}

		return $response;
	}

	public function getTransferInfo() {
		return $this->_transferInfo();
	}

	public function setTransferInfo(array $transferInfo) {
		$this->_transferInfo = $transferInfo;
	}

	/**
	 * This is only available if DEBUG constant is set to true
	 *
	 * @return float Query time
	 */
	public function getQueryTime() {
		return $this->_queryTime;
	}

	public function setQueryTime($queryTime) {
		return $this->_queryTime = $queryTime;
	}
}