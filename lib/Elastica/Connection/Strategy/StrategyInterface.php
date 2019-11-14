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
     * @param Connection[] $connections
     */
    public function getConnection(array $connections): Connection;
}
