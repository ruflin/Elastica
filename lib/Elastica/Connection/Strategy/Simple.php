<?php

namespace Elastica\Connection\Strategy;

use Elastica\Connection\Strategy\StrategyInterface;
use Elastica\Exception\ClientException;

/**
 * Description of SimpleStrategy
 *
 * @author chabior
 */
class Simple implements StrategyInterface
{
    
    /**
     * @param array|\Elastica\Connection[] $connections
     * @return \Elastica\Connection
     * @throws \Elastica\Exception\ClientException
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
