<?php
namespace Elastica\Exception;

use Elastica\Request;
use Elastica\Response;

/**
 * Connection exception.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class ConnectionException extends \RuntimeException implements ExceptionInterface
{
    /**
     * @var Request Request object
     */
    protected $_request;

    /**
     * @var Response Response object
     */
    protected $_response;

    /**
     * Construct Exception.
     *
     * @param string   $message
     * @param Request  $request
     * @param Response $response
     */
    public function __construct(string $message, Request $request = null, Response $response = null)
    {
        $this->_request = $request;
        $this->_response = $response;

        parent::__construct($message);
    }

    /**
     * Returns request object.
     *
     * @return Request Request object
     */
    public function getRequest(): Request
    {
        return $this->_request;
    }

    /**
     * Returns response object.
     *
     * @return Response Response object
     */
    public function getResponse(): Response
    {
        return $this->_response;
    }
}
