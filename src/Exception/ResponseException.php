<?php

namespace Elastica\Exception;

use Elastica\Request;
use Elastica\Response;

/**
 * Response exception.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class ResponseException extends \RuntimeException implements ExceptionInterface
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
     */
    public function __construct(Request $request, Response $response)
    {
        $this->_request = $request;
        $this->_response = $response;
        parent::__construct($response->getErrorMessage());
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

    /**
     * Returns elasticsearch exception.
     */
    public function getElasticsearchException(): ElasticsearchException
    {
        $response = $this->getResponse();

        return new ElasticsearchException($response->getStatus(), $response->getErrorMessage());
    }
}
