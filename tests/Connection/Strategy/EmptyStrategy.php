<?php

namespace Elastica\Test\Connection\Strategy;

use Elastica\Connection;
use Elastica\Connection\Strategy\StrategyInterface;

/**
 * Description of EmptyStrategy.
 *
 * @author chabior
 */
class EmptyStrategy implements StrategyInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConnection(array $connections): Connection
    {
        return new Connection();
    }
}
