<?php

namespace Elastica\Connection;

use Elastica\Client;
use Elastica\Connection;
use Elastica\Connection\Strategy\StrategyInterface;
use Exception;

/**
 * Description of ConnectionPool
 *
 * @author chabior
 */
class ConnectionPool
{
    /**
     * Connections array
     *
     * @var array|\Elastica\Connection[]
     */
    protected $_connections;

    /**
     * Strategy for connection
     *
     * @var \Elastica\Connection\Strategy\StrategyInterface
     */
    protected $_strategy;

    /**
     * Callback function called on connection fail
     *
     * @var callback
     */
    protected $_callback;

    /**
     * @param array                                           $connections
     * @param \Elastica\Connection\Strategy\StrategyInterface $strategy
     * @param callback                                        $callback
     */
    public function __construct(array $connections, StrategyInterface $strategy, $callback = null)
    {
        $this->_connections = $connections;

        $this->_strategy = $strategy;

        $this->_callback = $callback;
    }

    /**
     * @param \Elastica\Connection $connection
     */
    public function addConnection(Connection $connection)
    {
        $this->_connections[] = $connection;
    }

    /**
     * @param array|\Elastica\Connection[] $connections
     */
    public function setConnections(array $connections)
    {
        $this->_connections = $connections;
    }

    /**
     * @return boolean
     */
    public function hasConnection()
    {
        foreach ($this->_connections as $connection) {
            if ($connection->isEnabled()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array
     */
    public function getConnections()
    {
        return $this->_connections;
    }

    /**
     * @return \Elastica\Connection
     * @throws \Elastica\Exception\ClientException
     */
    public function getConnection()
    {
        return $this->_strategy->getConnection($this->getConnections());
    }

    /**
     * @param \Elastica\Connection $connection
     * @param \Exception           $e
     * @param Client               $client
     */
    public function onFail(Connection $connection, Exception $e, Client $client)
    {
        $connection->setEnabled(false);

        if ($this->_callback) {
            call_user_func($this->_callback, $connection, $e, $client);
        }
    }

    /**
     *
     * @return \Elastica\Connection\Strategy\StrategyInterface
     */
    public function getStrategy()
    {
        return $this->_strategy;
    }
}
