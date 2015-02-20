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
     * @throws \Elastica\Exception\ClientException
     *
     * @param  array|\Elastica\Connection[] $connections
     * @return \Elastica\Connection
     */
    public function getConnection($connections)
    {
        shuffle($connections);

        return parent::getConnection($connections);
    }
}
