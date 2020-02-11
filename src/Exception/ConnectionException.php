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
     * @var Request|null Request object
     */
    protected $_request;

    /**
     * @var Response|null Response object
     */
    protected $_response;

    /**
     * Construct Exception.
     */
    public function __construct(string $message, ?Request $request = null, ?Response $response = null)
    {
        $this->_request = $request;
        $this->_response = $response;

        parent::__construct($message);
    }

    /**
     * Returns request object.
     *
     * @return Request|null Request object
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * Returns response object.
     *
     * @return Response|null Response object
     */
    public function getResponse()
    {
        return $this->_response;
    }
}
