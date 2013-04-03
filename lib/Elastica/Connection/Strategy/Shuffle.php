<?php

namespace Elastica\Connection\Strategy;

use Elastica\Connection;
use Elastica\Connection\Pool;

class Shuffle extends Simple
{
    /**
     * @param Pool $pool
     * @return Connection|null
     */
    public function getConnection(Pool $pool)
    {
        $enabledConnection = null;

        $connections = $pool->getConnections();
        shuffle($connections);

        foreach ($pool->getConnections() as $connection) {
            if (!$this->_isDisabled($connection)) {
                $enabledConnection = $connection;
                break;
            }
        }

        return $enabledConnection;
    }
}


