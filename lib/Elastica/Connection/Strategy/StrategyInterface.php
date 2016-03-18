<?php

namespace Elastica\Connection\Strategy;

/**
 * Description of AbstractStrategy.
 *
 * @author chabior
 */
interface StrategyInterface
{
    /**
     * @param array|\Elastica\Connection[] $connections
     *
     * @return \Elastica\Connection
     */
    public function getConnection($connections);
}
