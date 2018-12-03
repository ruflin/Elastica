<?php
namespace Elastica\Connection\Strategy;

use Elastica\Connection;

/**
 * Description of AbstractStrategy.
 *
 * @author chabior
 */
interface StrategyInterface
{
    /**
     * @param array|Connection[] $connections
     *
     * @return Connection
     */
    public function getConnection(array $connections): Connection;
}
