<?php

namespace Elastica\Test\Connection\Strategy;

use Elastica\Connection\Strategy\StrategyFactory;
use Elastica\Test\Base;

/**
 * Description of StrategyFactoryTest
 *
 * @author chabior
 */
class StrategyFactoryTest extends Base
{
    public function testCreateCallbackStrategy()
    {
        $callback = function ($connections) {
        };

        $strategy = StrategyFactory::create($callback);

        $this->assertInstanceOf('Elastica\Connection\Strategy\CallbackStrategy', $strategy);
    }

    public function testCreateByName()
    {
        $strategyName = 'Simple';

        $strategy = StrategyFactory::create($strategyName);

        $this->assertInstanceOf('Elastica\Connection\Strategy\Simple', $strategy);
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

        $this->assertInstanceOf($strategyName, $strategy);
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
