<?php

namespace Elastica\Exception;

use Elastica\Request;
use Elastica\Response;

/**
 * Connection exception
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class ConnectionException extends \RuntimeException implements ExceptionInterface
{
    /**
     * Request
     *
     * @var \Elastica\Request Request object
     */
    protected $_request;

    /**
     * Response
     *
     * @var \Elastica\Response Response object
     */
    protected $_response;

    /**
     * Construct Exception
     *
     * @param string             $message  Message
     * @param \Elastica\Request  $request
     * @param \Elastica\Response $response
     */
    public function __construct($message, Request $request = null, Response $response = null)
    {
        $this->_request = $request;
        $this->_response = $response;

        parent::__construct($message);
    }

    /**
     * Returns request object
     *
     * @return \Elastica\Request Request object
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * Returns response object
     *
     * @return \Elastica\Response Response object
     */
    public function getResponse()
    {
        return $this->_response;
    }
}
