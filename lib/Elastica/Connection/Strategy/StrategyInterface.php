<?php

namespace Elastica\Connection\Strategy;

use Elastica\Connection;
use Elastica\Connection\Pool;
use Elastica\Exception\ConnectionException;

interface StrategyInterface
{
    /**
     * @param \Elastica\Connection\Pool $pool
     * @return Connection|null
     */
    public function getConnection(Pool $pool);

    /**
     * @param Connection $connection
     * @param \Elastica\Exception\ConnectionException $connectionException
     * @return
     */
    public function onFail(Connection $connection, ConnectionException $connectionException);

    /**
     * @param \Elastica\Connection $connection
     * @param Connection $connection
     */
    public function onSuccess(Connection $connection);
}
