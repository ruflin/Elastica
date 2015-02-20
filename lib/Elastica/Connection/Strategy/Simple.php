<?php

namespace Elastica\Connection\Strategy;

use Elastica\Exception\ClientException;

/**
 * Description of SimpleStrategy
 *
 * @author chabior
 */
class Simple implements StrategyInterface
{
    /**
     * @throws \Elastica\Exception\ClientException
     *
     * @param  array|\Elastica\Connection[] $connections
     * @return \Elastica\Connection
     */
    public function getConnection($connections)
    {
        foreach ($connections as $connection) {
            if ($connection->isEnabled()) {
                return $connection;
            }
        }

        throw new ClientException('No enabled connection');
    }
}
