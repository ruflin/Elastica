<?php

namespace Elastica\Connection\Strategy;

/**
 * Description of CallbackStrategy
 *
 * @author chabior
 */
class CallbackStrategy implements StrategyInterface
{
    /**
     *
     * @var Closure 
     */
    protected $callback;
    /**
     * 
     * @param Closure $callback
     * @throws \InvalidArgumentException
     */
    public function __construct($callback)
    {
        if (!self::isValid($callback)) {
            throw new \InvalidArgumentException(sprintf('Callback should be a Closure, %s given!', gettype($callback)));
        }
        
        $this->callback = $callback;
    }
    /**
     * 
     * @param array|\Elastica\Connection[] $connections
     * @return \Elastica\Connection
     */
    public function getConnection($connections)
    {
        return $this->callback->__invoke($connections);
    }
    /**
     * 
     * @return boolean
     */
    public static function isValid($callback)
    {
        return is_object($callback) && ($callback instanceof \Closure);
    }
}
