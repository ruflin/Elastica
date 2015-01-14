<?php

namespace Elastica\Connection\Strategy;

/**
 * Description of RoundRobin
 *
 * @author chabior
 */
class RoundRobin extends Simple
{
    /**
     * @param  array|\Elastica\Connection[]        $connections
     * @return \Elastica\Connection
     * @throws \Elastica\Exception\ClientException
     */
    public function getConnection($connections)
    {
        shuffle($connections);

        return parent::getConnection($connections);
    }
}
