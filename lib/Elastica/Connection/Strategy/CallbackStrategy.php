<?php
namespace Elastica\Connection\Strategy;

use Elastica\Exception\InvalidException;

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
    public function __construct($callback)
    {
        if (!self::isValid($callback)) {
            throw new InvalidException(sprintf('Callback should be a callable, %s given!', gettype($callback)));
        }

        $this->_callback = $callback;
    }

    /**
     * @param array|\Elastica\Connection[] $connections
     *
     * @return \Elastica\Connection
     */
    public function getConnection($connections)
    {
        return call_user_func_array($this->_callback, array($connections));
    }

    /**
     * @param callable $callback
     *
     * @return bool
     */
    public static function isValid($callback)
    {
        return is_callable($callback);
    }
}
