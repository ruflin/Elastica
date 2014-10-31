<?php

namespace Elastica\Test\Connection\Strategy;

use Elastica\Connection\Strategy\CallbackStrategy;
use Elastica\Test\Base;

/**
 * Description of CallbackStrategyTest
 *
 * @author chabior
 */
class CallbackStrategyTest extends Base
{
    public function testInvoke()
    {
        $count = 0;
        
        $callback = function ($connections) use(&$count) {
            $count++;
        };
        
        $strategy = new CallbackStrategy($callback);
        $strategy->getConnection(array());
        
        $this->assertEquals(1, $count);
    }
    
    public function testIsValid()
    {
        $callback = function(){};
        
        $isValid = CallbackStrategy::isValid($callback);
        
        $this->assertTrue($isValid);
    }
    
    public function testFailIsValid()
    {
        $callback = new \stdClass();
        
        $isValid = CallbackStrategy::isValid($callback);
        
        $this->assertFalse($isValid);
    }
    
    public function testConnection()
    {
        $count = 0;
        
        $config = array('connectionStrategy' => function ($connections) use(&$count) {
            ++$count;
            return current($connections);
       });
       
       $client = new \Elastica\Client($config);
       $resonse = $client->request('/_aliases');
       
       $this->assertEquals(1, $count);
       
       $this->assertTrue($resonse->isOk());
       
       $strategy = $client->getConnectionStrategy();
       
       $condition = ($strategy instanceof CallbackStrategy);
       
       $this->assertTrue($condition);
    }
}
