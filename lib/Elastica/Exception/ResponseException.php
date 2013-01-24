<?php

namespace Elastica\Exception;

use Elastica\Response;

/**
 * Response exception
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class ResponseException extends AbstractException
{
    /**
     * Response
     *
     * @var \Elastica\Response Response object
     */
    protected $_response = null;

    /**
     * Construct Exception
     *
     * @param \Elastica\Response $response
     */
    public function __construct(Response $response)
    {
        $this->_response = $response;
        parent::__construct($response->getError());
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
