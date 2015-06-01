<?php
namespace Elastica\Test\Connection\Strategy;

use Elastica\Connection\Strategy\StrategyFactory;
use Elastica\Test\Base;

/**
 * Description of StrategyFactoryTest.
 *
 * @author chabior
 */
class StrategyFactoryTest extends Base
{
    /**
     * @group unit
     */
    public function testCreateCallbackStrategy()
    {
        $callback = function ($connections) {
        };

        $strategy = StrategyFactory::create($callback);

        $this->assertInstanceOf('Elastica\Connection\Strategy\CallbackStrategy', $strategy);
    }

    /**
     * @group unit
     */
    public function testCreateByName()
    {
        $strategyName = 'Simple';

        $strategy = StrategyFactory::create($strategyName);

        $this->assertInstanceOf('Elastica\Connection\Strategy\Simple', $strategy);
    }

    /**
     * @group unit
     */
    public function testCreateByClass()
    {
        $strategy = new EmptyStrategy();

        $this->assertEquals($strategy, StrategyFactory::create($strategy));
    }

    /**
     * @group unit
     */
    public function testCreateByClassName()
    {
        $strategyName = '\\Elastica\Test\Connection\Strategy\\EmptyStrategy';

        $strategy = StrategyFactory::create($strategyName);

        $this->assertInstanceOf($strategyName, $strategy);
    }

    /**
     * @group unit
     * @expectedException \InvalidArgumentException
     */
    public function testFailCreate()
    {
        $strategy = new \stdClass();

        StrategyFactory::create($strategy);
    }
}
