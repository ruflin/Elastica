<?php

namespace Elastica\Connection\Strategy;

use Elastica\Connection\Strategy\Simple;

/**
 * Description of Request
 *
 * @author chabior
 */
class Request extends Simple
{
    /**
     * 
     * @param array|\Elastica\Connection[] $connections
     * @return \Elastica\Connection
     */
    public function getConnection($connections)
    {
        static $connection = null;
        
        if (null === $connection) {
            $connection = parent::getConnection($connections);
        }
        
        return $connection;
    }
}
