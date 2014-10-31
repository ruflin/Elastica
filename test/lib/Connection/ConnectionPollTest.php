<?php

namespace Elastica\Test\Connection;

use Elastica\Test\Base as BaseTest;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

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
        
        $this->assertTrue($pool->getConnection() instanceof \Elastica\Connection);
    }
    
    protected function getConnections($quantity = 1)
    {
        $params = array();
        $connections = array();
        
        for ($i = 0; $i<$quantity; $i++) {
            $connections[] = new \Elastica\Connection($params);
        }
        
        return $connections;
    }
    
    protected function createPool()
    {
        $connections = $this->getConnections();
        $strategy = \Elastica\Connection\Strategy\StrategyFactory::create('Simple');
        
        $pool = new \Elastica\Connection\ConnectionPool($connections, $strategy);
        
        return $pool;
    }
}
