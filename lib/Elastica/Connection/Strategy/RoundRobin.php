<?php
namespace Elastica\Connection\Strategy;

use Elastica\Connection;
use Elastica\Exception\ClientException;

/**
 * Description of RoundRobin.
 *
 * @author chabior
 */
class RoundRobin extends Simple
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
        shuffle($connections);

        return parent::getConnection($connections);
    }
}
