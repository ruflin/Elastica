<?php
/**
 * Transport exception
 *
 * @category Xodoa
 * @package Elastica
 * @author Mikhail Shamin <munk13@gmail.com>
 */
class Elastica_Exception_Transport_Thrift extends Elastica_Exception_Transport
{
    /**
     * @var TException
     */
    protected $_thriftException;

    /**
     * @param TException $thriftException
     * @param Elastica_Request $request
     * @param Elastica_Response $response
     */
    public function __construct(TException $thriftException, Elastica_Request $request = null, Elastica_Response $response = null)
    {
        $this->_thriftException = $thriftException;
        $message = $this->getErrorMessage($this->getThriftException());
        parent::__construct($message, $request, $response);
    }

    /**
     * @param TException $thriftException
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