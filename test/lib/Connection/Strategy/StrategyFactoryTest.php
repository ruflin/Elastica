<?php

namespace Elastica\Test\Connection\Strategy;

use Elastica\Connection\Strategy\CallbackStrategy;
use Elastica\Connection\Strategy\Simple;
use Elastica\Connection\Strategy\StrategyFactory;
use Elastica\Test\Base;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of StrategyFactoryTest
 *
 * @author chabior
 */
class StrategyFactoryTest extends Base
{
   public function testCreateCallbackStrategy()
   {
       $callback = function ($connections)
       {
           
       };
       
       $strategy = StrategyFactory::create($callback);
       
       $condition = $strategy instanceof CallbackStrategy;
       
       $this->assertTrue($condition);
   }
   
   public function testCreateByName()
   {
       $strategyName = 'Simple';
       
       $strategy = StrategyFactory::create($strategyName);
       
       $this->assertTrue($strategy instanceof Simple);
   }
   
   public function testCreateByClass()
   {
       $strategy = new EmptyStrategy();
       
       $this->assertEquals($strategy, StrategyFactory::create($strategy));
   }
   
   public function testCreateByClassName()
   {
       $strategyName = '\\Elastica\Test\Connection\Strategy\\EmptyStrategy';
       
       $strategy = StrategyFactory::create($strategyName);
       
       $condition = $strategy instanceof $strategyName;
       
       $this->assertTrue($condition);
   }
   /**
    * @expectedException \InvalidArgumentException
    */
   public function testFailCreate()
   {
       $strategy = new \stdClass();
       
       StrategyFactory::create($strategy);
   }
}
