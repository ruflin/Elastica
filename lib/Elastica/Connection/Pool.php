<?php

namespace Elastica\Connection;

use Elastica\Connection;
use Elastica\Connection\Strategy\StrategyInterface;
use Elastica\Exception\ConnectionException;
use Elastica\Connection\Strategy\StrategyFactory;

class Pool
{
    /**
     * @var StrategyInterface
     */
    protected $_strategy;

    /**
     * @var Connection[]
     */
    protected $connections = array();

    /**
     * @var
     */
    protected $_callback;

    /**
     * @param array $connections
     * @param StrategyInterface $strategy
     * @param Callable $callback
     */
    public function __construct(array $connections, StrategyInterface $strategy, $callback = null)
    {
        $this->setConnections($connections);
        $this->_strategy = $strategy;
        $this->_callback = $callback;
    }

    /**
     * @param array $config
     * @param Callable $callback
     * @return \Elastica\Connection\Pool
     */
    public static function create(array $config, $callback = null)
    {
        $connections = array();

        if (isset($config['connections']) && is_array($config['connections'])) {
            foreach ($config['connections'] as $connectionConfig) {
                $connections[] = Connection::create($connectionConfig);
            }
        }

        if (isset($config['servers'])) {
            $connections[] = Connection::create($config['servers']);
        }

        // If no connections set, create default connection
        if (empty($connections)) {
            $params = array(
                'config' => array(),
            );
            foreach ($config as $key => $value) {
                if (in_array($key, array('curl', 'headers', 'url'))) {
                    $params['config'][$key] = $value;
                } else {
                    $params[$key] = $value;
                }
            }
            $connections[] = Connection::create($params);
        }

        $strategyFactory = new StrategyFactory($config);
        $strategy = $strategyFactory->create();

        return new self($connections, $strategy, $callback);
    }

    /**
     * @param Callable $callback
     */
    public function setCallback($callback)
    {
        $this->_callback = $callback;
    }

    /**
     * @param Connection[] $connections
     */
    public function setConnections(array $connections)
    {
        $this->clearConnections();
        $this->addConnections($connections);
    }

    /**
     * @param Connection[] $connections
     */
    public function addConnections(array $connections)
    {
        foreach ($connections as $connection) {
            $this->addConnection($connection);
        }
    }

    /**
     * @param Connection $connection
     */
    public function addConnection(Connection $connection)
    {
        $this->connections[] = $connection;
    }

    /**
     *
     */
    public function clearConnections()
    {
        $this->connections = array();
    }

    /**
     * @return bool
     */
    public function hasConnections()
    {
        return !empty($this->connections);
    }

    /**
     * @return array|\Elastica\Connection[]
     */
    public function getConnections()
    {
        return $this->connections;
    }

    /**
     * @return Connection
     */
    public function getConnection()
    {
        return $this->_strategy->getConnection($this);
    }

    /**
     * @param Connection $connection
     * @param ConnectionException $connectionException
     */
    public function onFail(Connection $connection, ConnectionException $connectionException)
    {
        $connection->setEnabled(false);

        // Calls callback with connection as param to make it possible to persist invalid connections
        if ($this->_callback) {
            call_user_func($this->_callback, $connection, $connectionException);
        }

        $this->_strategy->onFail($connection, $connectionException);
    }

    /**
     * @param Connection $connection
     */
    public function onSuccess(Connection $connection)
    {
        $this->_strategy->onSuccess($connection);
    }
}
