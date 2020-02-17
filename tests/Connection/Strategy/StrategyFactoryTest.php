<?php

namespace Elastica\Test\Connection\Strategy;

use Elastica\Connection\Strategy\CallbackStrategy;
use Elastica\Connection\Strategy\Simple;
use Elastica\Connection\Strategy\StrategyFactory;
use Elastica\Test\Base;

/**
 * Description of StrategyFactoryTest.
 *
 * @author chabior
 *
 * @internal
 */
class StrategyFactoryTest extends Base
{
    /**
     * @group unit
     */
    public function testCreateCallbackStrategy(): void
    {
        $callback = function ($connections): void {
        };

        $strategy = StrategyFactory::create($callback);

        $this->assertInstanceOf(CallbackStrategy::class, $strategy);
    }

    /**
     * @group unit
     */
    public function testCreateByName(): void
    {
        $strategyName = 'Simple';

        $strategy = StrategyFactory::create($strategyName);

        $this->assertInstanceOf(Simple::class, $strategy);
    }

    /**
     * @group unit
     */
    public function testCreateByClass(): void
    {
        $strategy = new EmptyStrategy();

        $this->assertEquals($strategy, StrategyFactory::create($strategy));
    }

    /**
     * @group unit
     */
    public function testCreateByClassName(): void
    {
        $strategy = StrategyFactory::create(EmptyStrategy::class);

        $this->assertInstanceOf(EmptyStrategy::class, $strategy);
    }

    /**
     * @group unit
     */
    public function testFailCreate(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $strategy = new \stdClass();

        StrategyFactory::create($strategy);
    }

    /**
     * @group unit
     */
    public function testNoCollisionWithGlobalNamespace(): void
    {
        // create collision
        if (!\class_exists('Simple')) {
            \class_alias('Elastica\Util', 'Simple');
        }
        $strategy = StrategyFactory::create('Simple');
        $this->assertInstanceOf(Simple::class, $strategy);
    }
}
