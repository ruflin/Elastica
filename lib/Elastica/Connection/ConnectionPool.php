<?php

namespace Elastica\Connection;

use Elastica\Client;
use Elastica\Connection;
use Elastica\Connection\Strategy\StrategyInterface;
use Exception;

/**
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
     * @throws \Elastica\Exception\ClientException
     *
     * @return ConnectionInterface
     */
    public function getConnection()
    {
        return $this->_strategy->getConnection($this->_connections);
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
