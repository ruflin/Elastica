<?php

namespace Elastica\Connection;

use Elastica\Client;
use Elastica\Connection;
use Elastica\Connection\Strategy\StrategyInterface;
use Elastica\Exception\ClientException;
use Exception;

/**
 * Description of ConnectionPool.
 *
 * @author chabior
 */
class ConnectionPool
{
    /**
     * @var array|Connection[] Connections array
     */
    protected $_connections;

    /**
     * @var StrategyInterface Strategy for connection
     */
    protected $_strategy;

    /**
     * @var callable Function called on connection fail
     */
    protected $_callback;

    /**
     * @param array             $connections
     * @param StrategyInterface $strategy
     * @param callback|null     $callback
     */
    public function __construct(array $connections, StrategyInterface $strategy, callable $callback = null)
    {
        $this->_connections = $connections;

        $this->_strategy = $strategy;

        $this->_callback = $callback;
    }

    /**
     * @param Connection $connection
     *
     * @return $this
     */
    public function addConnection(Connection $connection): self
    {
        $this->_connections[] = $connection;

        return $this;
    }

    /**
     * @param Connection[] $connections
     *
     * @return $this
     */
    public function setConnections(array $connections): self
    {
        $this->_connections = $connections;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasConnection(): bool
    {
        foreach ($this->_connections as $connection) {
            if ($connection->isEnabled()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return Connection[]
     */
    public function getConnections(): array
    {
        return $this->_connections;
    }

    /**
     * @throws ClientException
     *
     * @return Connection
     */
    public function getConnection(): Connection
    {
        return $this->_strategy->getConnection($this->getConnections());
    }

    /**
     * @param Connection $connection
     * @param \Exception $e
     * @param Client     $client
     */
    public function onFail(Connection $connection, Exception $e, Client $client)
    {
        $connection->setEnabled(false);

        if ($this->_callback) {
            \call_user_func($this->_callback, $connection, $e, $client);
        }
    }

    /**
     * @return StrategyInterface
     */
    public function getStrategy(): StrategyInterface
    {
        return $this->_strategy;
    }
}
