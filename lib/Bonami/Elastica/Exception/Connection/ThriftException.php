<?php
namespace Bonami\Elastica\Exception\Connection;

use Bonami\Elastica\Exception\ConnectionException;
use Bonami\Elastica\Request;
use Bonami\Elastica\Response;
use Thrift\Exception\TException;

/**
 * Transport exception.
 *
 * @author Mikhail Shamin <munk13@gmail.com>
 *
 * @deprecated Will be removed with elasticsearch 2.0
 */
class ThriftException extends ConnectionException
{
    /**
     * @var TException
     */
    protected $_thriftException;

    /**
     * @param \Thrift\Exception\TException $thriftException
     * @param \Bonami\Elastica\Request            $request
     * @param \Bonami\Elastica\Response           $response
     */
    public function __construct(TException $thriftException, Request $request = null, Response $response = null)
    {
        $this->_thriftException = $thriftException;
        $message = $this->getErrorMessage($this->getThriftException());
        parent::__construct($message, $request, $response);
    }

    /**
     * @param \Thrift\Exception\TException $thriftException
     *
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
