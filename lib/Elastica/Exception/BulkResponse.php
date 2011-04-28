<?php
/**
 * Bulk Response exception
 *
 * @category Xodoa
 * @package Elastica
 */
class Elastica_Exception_BulkResponse extends Elastica_Exception_Abstract {

	protected $_response = null;

	/**
	 * @param Elastica_Response $response
	 */
	public function __construct(Elastica_Response $response) {
		$this->_response = $response;
		parent::__construct('Error in one or more bulk request actions');
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
	 * Returns array of failed actions
	 *
	 * @return array Array of failed actions
	 */
	public function getFailures() {
		$data = $this->_response->getData();
		$errors = array();

		foreach($data['items'] as $item) {
			$meta = reset($item);
			$action = key($item);
			if(isset($meta['error'])) {
				$error = array(
					'action' => $action,
				);
				foreach($meta as $key => $value) {
					$key = ltrim($key, '_');
					$error[$key] = $value;
				}

				$errors[] = $error;
			}
		}

		return $errors;
	}
}
