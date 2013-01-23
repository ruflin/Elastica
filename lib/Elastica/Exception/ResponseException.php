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
     * @param \Elastica\Response $responseSet
     */
    public function __construct(Response $responseSet)
    {
        $this->_response = $responseSet;
        parent::__construct($responseSet->getError());
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
