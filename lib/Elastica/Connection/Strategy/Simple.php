<?php

namespace Elastica\Connection\Strategy;

use Elastica\Connection;
use Elastica\Connection\Pool;
use Elastica\Exception\ConnectionException;

class Simple implements StrategyInterface
{
    /**
     * @var array
     */
    protected $_disabled = array();

    /**
     * @param Connection $connection
     * @return bool
     */
    protected function _isDisabled(Connection $connection)
    {
        return in_array($connection, $this->_disabled, true);
    }

    /**
     * @param \Elastica\Connection\Pool $pool
     * @return Connection|null
     */
    public function getConnection(Pool $pool)
    {
        $enabledConnection = null;

        foreach ($pool->getConnections() as $connection) {
            if (!$this->_isDisabled($connection)) {
                $enabledConnection = $connection;
                break;
            }
        }

        return $enabledConnection;
    }

    /**
     * @param Connection $connection
     * @param ConnectionException $connectionException
     */
    public function onFail(Connection $connection, ConnectionException $connectionException)
    {
        $this->_disabled[] = $connection;
    }

    /**
     * @param Connection $connection
     * @return \Elastica\Connection|void
     */
    public function onSuccess(Connection $connection)
    {
    }
}


