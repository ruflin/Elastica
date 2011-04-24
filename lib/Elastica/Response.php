<?php
/**
 * Elastica Response object
 *
 * Stores query time, and result array -> is given to resultset, returned by ...
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class Elastica_Response {

	protected $_queryTime = null;
	protected $_responseString = '';
	protected $_error = false;
	protected $_transferInfo = array();

	/**
	 * @param string $responseString Response string (json)
	 */
	public function __construct($responseString) {
		$this->_responseString = $responseString;
	}

	/**
	 * @return string Error message
	 */
	public function getError() {
		$message = '';
		$response = $this->getData();

		if (isset($response['error'])) {
			$message = $response['error'];
		}
		return $message;
	}

	/**
	 * @return bool True if response has error
	 */
	public function hasError() {
		$response = $this->getData();

		if (isset($response['error'])) {
			return $response['error'];
		}
		return false;
	}

	/**
	 * @return array Response data array
	 */
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

	/**
	 * @return array Transfer info
	 */
	public function getTransferInfo() {
		return $this->_transferInfo;
	}

	/**
	 * Sets the transfer info
	 *
	 * @param array $transferInfo Transfer info
	 * @return Elastica_Response Current object
	 */
	public function setTransferInfo(array $transferInfo) {
		$this->_transferInfo = $transferInfo;
		return $this;
	}

	/**
	 * This is only available if DEBUG constant is set to true
	 *
	 * @return float Query time
	 */
	public function getQueryTime() {
		return $this->_queryTime;
	}

	/**
	 * Sets the query time
	 *
	 * @param float $queryTime Query time
	 * @return Elastica_Response Current object
	 */
	public function setQueryTime($queryTime) {
		$this->_queryTime = $queryTime;
		return $this;
	}
}