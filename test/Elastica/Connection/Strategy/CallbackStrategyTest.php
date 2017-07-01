<?php
namespace Elastica\Test\Connection\Strategy;

use Elastica\Connection\Strategy\CallbackStrategy;
use Elastica\Test\Base;

/**
 * Description of CallbackStrategyTest.
 *
 * @author chabior
 */
class CallbackStrategyTest extends Base
{
    /**
     * @group unit
     */
    public function testInvoke()
    {
        $count = 0;

        $callback = function ($connections) use (&$count) {
            ++$count;
        };

        $strategy = new CallbackStrategy($callback);
        $strategy->getConnection([]);

        $this->assertEquals(1, $count);
    }

    /**
     * @group unit
     */
    public function testIsValid()
    {
        // closure is valid
        $isValid = CallbackStrategy::isValid(function () {});
        $this->assertTrue($isValid);

        // object implementing __invoke
        $isValid = CallbackStrategy::isValid(new CallbackStrategyTestHelper());
        $this->assertTrue($isValid);

        // static method as string
        $isValid = CallbackStrategy::isValid(CallbackStrategyTestHelper::class.'::getFirstConnectionStatic');
        $this->assertTrue($isValid);

        // static method as array
        $isValid = CallbackStrategy::isValid([CallbackStrategyTestHelper::class, 'getFirstConnectionStatic']);
        $this->assertTrue($isValid);

        // object method
        $isValid = CallbackStrategy::isValid([new CallbackStrategyTestHelper(), 'getFirstConnectionStatic']);
        $this->assertTrue($isValid);

        // function name
        $isValid = CallbackStrategy::isValid('array_pop');
        $this->assertTrue($isValid);
    }

    /**
     * @group unit
     */
    public function testFailIsValid()
    {
        $isValid = CallbackStrategy::isValid(new \stdClass());
        $this->assertFalse($isValid);

        $isValid = CallbackStrategy::isValid('array_pop_pop_pop_pop_pop_pop');
        $this->assertFalse($isValid);
    }

    /**
     * @group functional
     */
    public function testConnection()
    {
        $count = 0;

        $config = ['connectionStrategy' => function ($connections) use (&$count) {
            ++$count;

            return current($connections);
       }];

        $client = $this->_getClient($config);
        $response = $client->request('/_aliases');

        $this->assertEquals(1, $count);

        $this->assertTrue($response->isOk());

        $strategy = $client->getConnectionStrategy();

        $this->assertInstanceOf(CallbackStrategy::class, $strategy);
    }
}
