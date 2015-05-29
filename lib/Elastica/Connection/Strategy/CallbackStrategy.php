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
     * @var Closure
     */
    protected $_callback;

    /**
     * @param Closure $callback
     *
     * @throws \Elastica\Exception\InvalidException
     */
    public function __construct($callback)
    {
        if (!self::isValid($callback)) {
            throw new InvalidException(sprintf('Callback should be a Closure, %s given!', gettype($callback)));
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
        return $this->_callback->__invoke($connections);
    }

    /**
     * @return bool
     */
    public static function isValid($callback)
    {
        return is_object($callback) && ($callback instanceof \Closure);
    }
}
