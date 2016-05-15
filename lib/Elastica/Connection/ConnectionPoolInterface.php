<?php

namespace Elastica\Connection;

use Elastica\Client;
use Elastica\Connection\Strategy\StrategyInterface;
use Exception;

interface ConnectionPoolInterface
{
    /**
     * Returns a connection otherwise throwing a ClientException if a connection
     * is not available.
     *
     * @throws \Elastica\Exception\ClientException
     * @return ConnectionInterface
     */
    public function getConnection();

    /**
     * Returns the connection strategy used by the pool.
     *
     * @return StrategyInterface
     */
    public function getStrategy();

    /**
     * If a connection is available.
     *
     * @return bool
     */
    public function hasConnection();

    /**
     * To be called when a connection fails allowing a ConnectionPool to manage
     * its connections.
     *
     * @param ConnectionInterface $connection
     * @param \Exception $e
     * @param Client $client
     */
    public function onFail(ConnectionInterface $connection, Exception $e, Client $client);
}
