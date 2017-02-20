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
     * @var \Elastica\Request Request object
     */
    protected $_request;

    /**
     * @var \Elastica\Response Response object
     */
    protected $_response;

    /**
     * Construct Exception.
     *
     * @param \Elastica\Request  $request
     * @param \Elastica\Response $response
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
     * @return \Elastica\Request Request object
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * Returns response object.
     *
     * @return \Elastica\Response Response object
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * Returns elasticsearch exception.
     *
     * @return ElasticsearchException
     */
    public function getElasticsearchException()
    {
        $response = $this->getResponse();

        return new ElasticsearchException($response->getStatus(), $response->getErrorMessage());
    }
}
