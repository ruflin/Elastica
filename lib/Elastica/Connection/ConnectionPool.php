<?php

namespace Elastica\Connection;

use Elastica\Connection;
use Elastica\Connection\Strategy\StrategyInterface;

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
     * @var  Elastica\Connection\Strategy\StrategyInterface
     */
    protected $strategy;
    /**
     * 
     * @param array|Connection[] $connections
     */
    public function __construct(array $connections, StrategyInterface $strategy)
    {
        $this->connections = $connections;
        
        $this->strategy = $strategy;
    }
    /**
     * 
     * @param \Elastica\Connection $connection
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
     * @return \Elastica\Connection
     */
    public function getConnection()
    {
        return $this->strategy->getConnection($this->getConnections());
    }
}
