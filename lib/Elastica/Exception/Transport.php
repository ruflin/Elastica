<?php
/**
 * Transport exception
 *
 * @category Xodoa
 * @package Elastica
 * @author Mikhail Shamin <munk13@gmail.com>
 */
class Elastica_Exception_Transport extends Elastica_Exception_Abstract
{
    /**
     * Request
     *
     * @var Elastica_Request Request object
     */
    protected $_request = null;

    /**
     * Response
     *
     * @var Elastica_Response Response object
     */
    protected $_response = null;

    /**
     * Construct Exception
     *
     * @param string            $message    Error
     * @param Elastica_Request  $request
     * @param Elastica_Response $response
     */
    public function __construct($message, Elastica_Request $request = null, Elastica_Response $response = null)
    {
        $this->_request = $request;
        $this->_response = $response;

        parent::__construct($message);
    }

    /**
     * Returns request object
     *
     * @return Elastica_Transport_Abstract Request object
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * Returns response object
     *
     * @return Elastica_Response Response object
     */
    public function getResponse()
    {
        return $this->_response;
    }
}