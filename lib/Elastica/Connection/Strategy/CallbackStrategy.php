<?php
namespace Elastica\Connection\Strategy;

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

    /**
     * @param callable $callback
     *
     * @throws \Elastica\Exception\InvalidException
     */
    public function __construct(callable $callback)
    {
        $this->_callback = $callback;
    }

    /**
     * @param array|\Elastica\Connection[] $connections
     *
     * @return \Elastica\Connection
     */
    public function getConnection(array $connections)
    {
        return call_user_func_array($this->_callback, [$connections]);
    }
}
