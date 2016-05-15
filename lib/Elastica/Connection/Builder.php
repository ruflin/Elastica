<?php

namespace Elastica\Connection;

use Elastica\Connection;
use Elastica\Connection\Strategy\StrategyFactory;

class Builder
{
    /**
     * Creates a ConnectionPool from a $config array.
     *
     * @param array $config
     * @param callable $callback
     * @return ConnectionPoolInterface
     */
    public function buildPool(array $config, $callback)
    {
        $connections = $this->getConnectionsFromConfig($config);
        $strategy = $this->getStrategyFromConfig($config);

        return new ConnectionPool($connections, $strategy, $callback);
    }

    /**
     * Builds an array of connections from the legacy $config array.
     *
     * @param array $config
     * @return ConnectionInterface[]
     */
    public function getConnectionsFromConfig(array $config)
    {
        $connections = [];
        if (isset($config['connections'])) {
            foreach ($config['connections'] as $connection) {
                $connections[] = Connection::create($this->_prepareConnectionParams($connection));
            }
        }
        if (isset($config['servers'])) {
            foreach ($config['servers'] as $connection) {
                $connections[] = Connection::create($this->_prepareConnectionParams($connection));
            }
        }

        if (!$connections) {
            $connections[] = Connection::create($this->_prepareConnectionParams($config));
        }

        return $connections;
    }

    /**
     * Returns the StrategyInterface from the legacy $config array.
     *
     * @param array $config
     * @return Connection\Strategy\StrategyInterface
     */
    public function getStrategyFromConfig(array $config)
    {
        if (isset($config['connectionStrategy'])) {
            return StrategyFactory::create($config['connectionStrategy']);
        }

        if (isset($config['roundRobin'])) {
            return StrategyFactory::create('RoundRobin');
        }

        return StrategyFactory::create('Simple');
    }

    /**
     * Prepares legacy $config configuration for connection parameters.
     *
     * @param array $config
     * @return array
     */
    private function _prepareConnectionParams($config)
    {
        $params = array();
        $params['config'] = array();
        foreach ($config as $key => $value) {
            if (in_array($key, array('bigintConversion', 'curl', 'headers', 'url'))) {
                $params['config'][$key] = $value;
            } else {
                $params[$key] = $value;
            }
        }

        return $params;
    }
}
