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
        $this->addConnectionExceptions($connectionExceptions);
        $message.= $this->getErrorMessage();
        parent::__construct($message);
    }

    /**
     * @param ConnectionException $connectionException
     */
    public function addConnectionException(ConnectionException $connectionException)
    {
        $this->_connectionExceptions[] = $connectionException;
    }

    /**
     * @param \Elastica\Exception\ConnectionException[] $connectionExceptions
     */
    public function addConnectionExceptions(array $connectionExceptions)
    {
        foreach ($connectionExceptions as $connectionException) {
            $this->addConnectionException($connectionException);
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
                $message.= $connectionException->getMessage() . "\n";
            }
        }
        return $message;
    }
}
