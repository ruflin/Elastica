<?php
namespace Elastica\Test\Connection\Strategy;

class CallbackStrategyTestHelper
{
    public function __invoke($connections)
    {
        return $connections[0];
    }

    public function getFirstConnection($connections)
    {
        return $connections[0];
    }

    public static function getFirstConnectionStatic($connections)
    {
        return $connections[0];
    }
}
