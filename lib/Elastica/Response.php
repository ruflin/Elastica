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
	protected $_response = null;

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
			return true;
		}
		return false;
	}

	/**
	 * @return array Response data array
	 */
	public function getData() {

		if ($this->_response == null) {
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

			$this->_response = $response;
		}
		return $this->_response;
	}

	/**
	 * Gets the transfer information if in DEBUG mode.
	 *
	 * @return array Information about the curl request.
	 */
	public function getTransferInfo() {
		return $this->_transferInfo;
	}

	/**
	 * Sets the transfer info of the curl request. This function is called
	 * from the Elastica_Client::_callService only in debug mode.
	 *
	 * @param array $transferInfo The curl transfer information.
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

	/**
	 * @return int Time request took
	 */
	public function getEngineTime() {
		$data = $this->getData();

		if (!isset($data['took'])) {
			throw new Elastica_Exception_NotFound("Unable to find the field [took]from the response");
		}

		return $data['took'];
	}

	/**
	 * Get the _shard statistics for the response
	 *
	 * @return array
	 */
	public function getShardsStatistics() {
		$data = $this->getData();

		if (!isset($data['_shards'])) {
			throw new Elastica_Exception_NotFound("Unable to find the field [_shards] from the response");
		}

		return $data['_shards'];
	}

}
