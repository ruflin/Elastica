<?php

namespace Elastica\Connection\Strategy;

use Elastica\Connection;

/**
 * Description of RoundRobin.
 *
 * @author chabior
 */
class RoundRobin extends Simple
{
    /**
     * {@inheritdoc}
     */
    public function getConnection(array $connections): Connection
    {
        \shuffle($connections);

        return parent::getConnection($connections);
    }
}
