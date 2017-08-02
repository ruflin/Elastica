<?php
namespace Bonami\Elastica\Connection\Strategy;

use Bonami\Elastica\Exception\ClientException;

/**
 * Description of SimpleStrategy.
 *
 * @author chabior
 */
class Simple implements StrategyInterface
{
    /**
     * @param array|\Bonami\Elastica\Connection[] $connections
     *
     * @throws \Bonami\Elastica\Exception\ClientException
     *
     * @return \Bonami\Elastica\Connection
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
