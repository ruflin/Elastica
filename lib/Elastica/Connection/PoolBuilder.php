<?php

namespace Elastica\Connection;

use Elastica\Connection;
use Elastica\Connection\Strategy\StrategyFactory;

class PoolBuilder
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
        $config = $this->_prepareConfig($config);

        $connections = [];
        foreach ($config['connections'] as $connection) {
            $connections[] = Connection::create($connection);
        }

        $connectionStrategy = StrategyFactory::create($config['connectionStrategy']);

        return new ConnectionPool($connections, $connectionStrategy, $callback);
    }

    /**
     * Prepares a $config array for processing.
     *
     * @param array $config
     * @return array
     */
    private function _prepareConfig(array $config)
    {
        $strategy = isset($config['connectionStrategy'])
            ? $config['connectionStrategy']
            : (isset($config['roundRobin'])
                ? 'RoundRobin'
                : 'Simple');

        $connections = [];
        if (isset($config['connections'])) {
            foreach ($config['connections'] as $connection) {
                $connections[] = $this->_prepareConnectionParams($connection);
            }
        }
        if (isset($config['servers'])) {
            foreach ($config['servers'] as $connection) {
                $connections[] = $this->_prepareConnectionParams($connection);
            }
        }

        if (!$connections) {
            $connections[] = $this->_prepareConnectionParams($config);
        }

        return [
            'connections' => $connections,
            'connectionStrategy' => $strategy
        ];
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
