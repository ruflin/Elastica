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
     *
     * @var array
     */
    protected $connections;
    /**
     *
     * @var  StrategyInterface
     */
    protected $strategy;
    /**
     *
     * @var callback
     */
    protected $callback;
    /**
     * 
     * @param array|Connection[] $connections
     */
    public function __construct(array $connections, StrategyInterface $strategy, $callback = null)
    {
        $this->connections = $connections;
        
        $this->strategy = $strategy;
        
        $this->callback = $callback;
    }
    /**
     * 
     * @param Connection $connection
     */
    public function addConnection(Connection $connection)
    {
        $this->connections[] = $connection;
    }
    /**
     * 
     * @param array|Connection[] $connections
     */
    public function setConnections(array $connections)
    {
        $this->connections = $connections;
    }
    /**
     * 
     * @return boolean
     */
    public function hasConnection()
    {
        foreach ($this->connections as $connection) {
            if ($connection->isEnabled()) {
                return true;
            }
        }
        
        return false;
    }
    /**
     * 
     * @return array
     */
    public function getConnections()
    {
        return $this->connections;
    }
    /**
     * 
     * @return Connection
     */
    public function getConnection()
    {
        return $this->strategy->getConnection($this->getConnections());
    }
    /**
     * 
     * @param Connection $connection
     * @param Exception $e
     * @param Client $client
     */
    public function onFail(Connection $connection, Exception $e, Client $client)
    {
        $connection->setEnabled(false);
        
        if ($this->callback) {
            call_user_func($this->callback, $connection, $e, $client);
        }
    }
}
