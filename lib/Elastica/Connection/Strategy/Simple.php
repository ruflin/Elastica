<?php
namespace Elastica\Connection\Strategy;

use Elastica\Connection;
use Elastica\Exception\ClientException;

/**
 * Description of SimpleStrategy.
 *
 * @author chabior
 */
class Simple implements StrategyInterface
{
    /**
     * @param array|Connection[] $connections
     *
     * @throws ClientException
     *
     * @return Connection
     */
    public function getConnection(array $connections): Connection
    {
        foreach ($connections as $connection) {
            if ($connection->isEnabled()) {
                return $connection;
            }
        }

        throw new ClientException('No enabled connection');
    }
}
