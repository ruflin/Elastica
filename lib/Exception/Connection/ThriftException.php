<?php

namespace Elastica\Exception\Connection;

use Elastica\Exception\ConnectionException;
use Elastica\Request;
use Elastica\Response;
use Thrift\Exception\TException;

/**
 * Transport exception
 *
 * @category Xodoa
 * @package Elastica
 * @author Mikhail Shamin <munk13@gmail.com>
 */
class ThriftException extends ConnectionException
{
    /**
     * @var TException
     */
    protected $_thriftException;

    /**
     * @param \Thrift\Exception\TException $thriftException
     * @param \Elastica\Request $request
     * @param \Elastica\Response $response
     */
    public function __construct(TException $thriftException, Request $request = null, Response $response = null)
    {
        $this->_thriftException = $thriftException;
        $message = $this->getErrorMessage($this->getThriftException());
        parent::__construct($message, $request, $response);
    }

    /**
     * @param \Thrift\Exception\TException $thriftException
     * @return string
     */
    public function getErrorMessage(TException $thriftException)
    {
        return $thriftException->getMessage();
    }
    /**
     * @return TException
     */
    public function getThriftException()
    {
        return $this->_thriftException;
    }
}
