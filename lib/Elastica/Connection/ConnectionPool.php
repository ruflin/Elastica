<?php

namespace Elastica\Connection;

use Elastica\Client;
use Elastica\Connection;
use Elastica\Connection\Strategy\StrategyInterface;
use Exception;

/**
 * Description of ConnectionPool.
 *
 * @author chabior
 */
class ConnectionPool implements ConnectionPoolInterface
{
    /**
     * @var ConnectionInterface
     */
    protected $_connections;

    /**
     * @var StrategyInterface
     */
    protected $_strategy;

    /**
     * @var callback Function called on connection fail
     */
    protected $_callback;

    /**
     * @param ConnectionInterface[] $connections
     * @param StrategyInterface $strategy
     * @param callback $callback
     */
    public function __construct(array $connections, StrategyInterface $strategy, $callback = null)
    {
        $this->_callback = $callback;
        $this->_connections = $connections;
        $this->_strategy = $strategy;
    }

    /**
     * @param ConnectionInterface $connection
     *
     * @return $this
     */
    public function addConnection(ConnectionInterface $connection)
    {
        $this->_connections[] = $connection;

        return $this;
    }

    /**
     * @param ConnectionInterface[] $connections
     *
     * @return $this
     */
    public function setConnections(array $connections)
    {
        $this->_connections = $connections;

        return $this;
    }

    /**
     * @return bool
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
     * @return ConnectionInterface[]
     */
    public function getConnections()
    {
        return $this->_connections;
    }

    /**
     * @throws \Elastica\Exception\ClientException
     *
     * @return ConnectionInterface
     */
    public function getConnection()
    {
        return $this->_strategy->getConnection($this->getConnections());
    }

    /**
     * @param ConnectionInterface $connection
     * @param \Exception           $e
     * @param Client               $client
     */
    public function onFail(ConnectionInterface $connection, Exception $e, Client $client)
    {
        $connection->setEnabled(false);

        if ($this->_callback) {
            call_user_func($this->_callback, $connection, $e, $client);
        }
    }

    /**
     * @return StrategyInterface
     */
    public function getStrategy()
    {
        return $this->_strategy;
    }
}
