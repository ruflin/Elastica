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
     * @var callable|null Function called on connection fail
     */
    protected $_callback;

    public function __construct(array $connections, StrategyInterface $strategy, ?callable $callback = null)
    {
        $this->_connections = $connections;
        $this->_strategy = $strategy;
        $this->_callback = $callback;
    }

    /**
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
     */
    public function getConnection(): Connection
    {
        return $this->_strategy->getConnection($this->getConnections());
    }

    public function onFail(Connection $connection, Exception $e, Client $client): void
    {
        $connection->setEnabled(false);

        if ($this->_callback) {
            ($this->_callback)($connection, $e, $client);
        }
    }

    public function getStrategy(): StrategyInterface
    {
        return $this->_strategy;
    }
}
