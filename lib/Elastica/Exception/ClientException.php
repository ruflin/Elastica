<?php

namespace Elastica\Exception;

/**
 * Client exception
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class ClientException extends AbstractException
{
    /**
     * @var \Elastica\Exception\ConnectionException[]
     */
    protected $_connectionExceptions = array();

    /**
     * @param string $message
     * @param \Elastica\Exception\ConnectionException[] $connectionExceptions
     */
    public function __construct($message, array $connectionExceptions = array())
    {
        $this->_addConnectionExceptions($connectionExceptions);
        $message.= $this->getErrorMessage();
        parent::__construct($message);
    }

    /**
     * @param ConnectionException $connectionException
     */
    protected function _addConnectionException(ConnectionException $connectionException)
    {
        $this->_connectionExceptions[] = $connectionException;
    }

    /**
     * @param \Elastica\Exception\ConnectionException[] $connectionExceptions
     */
    protected function _addConnectionExceptions(array $connectionExceptions)
    {
        foreach ($connectionExceptions as $connectionException) {
            $this->_addConnectionException($connectionException);
        }
    }

    /**
     * @return \Elastica\Exception\ConnectionException[]
     */
    public function getConnectionExceptions()
    {
        return $this->_connectionExceptions;
    }
    /**
     * @return bool
     */
    public function hasConnectionExceptions()
    {
        return !empty($this->_connectionExceptions);
    }

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        $message = '';
        if ($this->hasConnectionExceptions()) {
            $message.= "\n\nFollowing connection exceptions occurred:\n";
            foreach ($this->getConnectionExceptions() as $connectionException) {
                $message.= $this->_getTransportDsn($connectionException);
                $message.= $connectionException->getMessage() . "\n";
            }
        }
        return $message;
    }

    /**
     * @param \Elastica\Exception\ConnectionException $connectionException
     * @return string
     */
    protected function _getTransportDsn(ConnectionException $connectionException)
    {
        $dsn = '';
        $request = $connectionException->getRequest();
        if ($request) {
            $connection = $request->getConnection();
            if ($connection) {
                $transport = $connection->getTransportObject();
                if ($transport) {
                    $dsn .= $transport->getDsn() . ': ';
                }
            }
        }
        return $dsn;
    }
}
