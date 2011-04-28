<?php
/**
 * Response exception
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class Elastica_Exception_Response extends Elastica_Exception_Abstract {

	protected $_response = null;

	/**
	 * @param Elastica_Response $response
	 */
	public function __construct(Elastica_Response $response) {
		$this->_response = $response;
		parent::__construct($response->getError());
	}

	/**
	 * Returns reponsce object
	 *
	 * @return Elastica_Response Response object
	 */
	public function getResponse() {
		return $this->_response;
	}
}
