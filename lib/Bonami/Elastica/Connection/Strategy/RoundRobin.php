<?php
namespace Bonami\Elastica\Connection\Strategy;

/**
 * Description of RoundRobin.
 *
 * @author chabior
 */
class RoundRobin extends Simple
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
        shuffle($connections);

        return parent::getConnection($connections);
    }
}
