<?php

namespace Elastica\Connection\Strategy;

use Elastica\Connection;

/**
 * Description of CallbackStrategy.
 *
 * @author chabior
 */
class CallbackStrategy implements StrategyInterface
{
    /**
     * @var callable
     */
    protected $_callback;

    public function __construct(callable $callback)
    {
        $this->_callback = $callback;
    }

    /**
     * {@inheritdoc}
     */
    public function getConnection(array $connections): Connection
    {
        return ($this->_callback)($connections);
    }
}
