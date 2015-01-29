<?php

namespace Elastica\Test\Connection;

use Elastica\Connection;
use Elastica\Connection\ConnectionPool;
use Elastica\Connection\Strategy\StrategyFactory;
use Elastica\Test\Base as BaseTest;

/**
 * Description of ConnectionPollTest
 *
 * @author chabior
 */
class ConnectionPollTest extends BaseTest
{
    public function testConstruct()
    {
        $pool = $this->createPool();

        $this->assertEquals($this->getConnections(), $pool->getConnections());
    }

    public function testSetConnections()
    {
        $pool = $this->createPool();

        $connections = $this->getConnections(5);

        $pool->setConnections($connections);

        $this->assertEquals($connections, $pool->getConnections());
    }

    public function testHasConnection()
    {
        $pool = $this->createPool();

        $this->assertTrue($pool->hasConnection());
    }

    public function testFailHasConnections()
    {
        $pool = $this->createPool();

        $pool->setConnections(array());

        $this->assertFalse($pool->hasConnection());
    }

    public function testGetConnection()
    {
        $pool = $this->createPool();

        $this->assertInstanceOf('Elastica\Connection', $pool->getConnection());
    }

    protected function getConnections($quantity = 1)
    {
        $params = array();
        $connections = array();

        for ($i = 0; $i<$quantity; $i++) {
            $connections[] = new Connection($params);
        }

        return $connections;
    }

    protected function createPool()
    {
        $connections = $this->getConnections();
        $strategy = StrategyFactory::create('Simple');

        $pool = new ConnectionPool($connections, $strategy);

        return $pool;
    }
}
