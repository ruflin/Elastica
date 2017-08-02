<?php
namespace Bonami\Elastica\Connection\Strategy;

/**
 * Description of AbstractStrategy.
 *
 * @author chabior
 */
interface StrategyInterface
{
    /**
     * @param array|\Bonami\Elastica\Connection[] $connections
     *
     * @return \Bonami\Elastica\Connection
     */
    public function getConnection($connections);
}
