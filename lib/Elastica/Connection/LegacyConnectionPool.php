<?php

namespace Elastica\Connection;

use Elastica\Client;
use Elastica\Connection\Strategy\StrategyInterface;
use Elastica\Exception\InvalidException;
use Exception;

final class LegacyConnectionPool extends ConnectionPool
{
    /**
     * @var Builder
     */
    private $_builder;

    /**
     * Config with defaults.
     *
     * @var array
     */
    private $_config = array(
        'host' => null,
        'port' => null,
        'path' => null,
        'url' => null,
        'proxy' => null,
        'transport' => null,
        'persistent' => true,
        'timeout' => null,
        'connections' => array(), // host, port, path, timeout, transport, compression, persistent, timeout, config -> (curl, headers, url)
        'roundRobin' => false,
        'username' => null,
        'password' => null,
    );

    /**
     * @param array $config
     * @param Builder $builder
     * @param callback $callback
     */
    public function __construct(array $config, Builder $builder, $callback = null)
    {
        // Intentionally does not call parent constructor.

        $this->_builder = $builder;
        $this->_callback = $callback;

        $this->setConfig($config);
        $this->init();
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
     * Returns a specific config key or the whole
     * config array if not set.
     *
     * @param string $key Config key
     *
     * @throws \Elastica\Exception\InvalidException
     *
     * @return array|string Config value
     */
    public function getConfig($key = '')
    {
        if (empty($key)) {
            return $this->_config;
        }

        if (!array_key_exists($key, $this->_config)) {
            throw new InvalidException('Config key is not set: '.$key);
        }

        return $this->_config[$key];
    }

    /**
     * @param array|string $keys    config key or path of config keys
     * @param mixed        $default default value will be returned if key was not found
     *
     * @return mixed
     */
    public function getConfigValue($keys, $default = null)
    {
        $value = $this->_config;

        foreach ((array) $keys as $key) {
            if (!isset($value[$key])) {
                return $default;
            }

            $value = $value[$key];
        }

        return $value;
    }

    /**
     * @return ConnectionInterface
     */
    public function getConnection()
    {
        return $this->_strategy->getConnection($this->_connections);
    }

    /**
     * @return ConnectionInterface[]
     */
    public function getConnections()
    {
        return $this->_connections;
    }

    /**
     * @return StrategyInterface
     */
    public function getStrategy()
    {
        return $this->_strategy;
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
     * Resets the connection pool based on the internal _config array.
     */
    public function init()
    {
        $this->_connections = $this->_builder->getConnectionsFromConfig($this->_config);
        $this->_strategy = $this->_builder->getStrategyFromConfig($this->_config);
    }

    /**
     * @param ConnectionInterface $connection
     * @param Exception $e
     * @param Client $client
     */
    public function onFail(ConnectionInterface $connection, Exception $e, Client $client)
    {
        $connection->setEnabled(false);

        if ($this->_callback) {
            call_user_func($this->_callback, $connection, $e, $client);
        }
    }

    /**
     * Sets specific config values (updates and keeps default values).
     *
     * @param array $config Params
     *
     * @return $this
     */
    public function setConfig(array $config)
    {
        foreach ($config as $key => $value) {
            $this->_config[$key] = $value;
        }

        return $this;
    }

    /**
     * Sets / overwrites a specific config value.
     *
     * @param string $key   Key to set
     * @param mixed  $value Value
     *
     * @return $this
     */
    public function setConfigValue($key, $value)
    {
        return $this->setConfig(array($key => $value));
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
}
