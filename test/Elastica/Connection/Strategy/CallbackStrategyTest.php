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
        $response = $client->request('_aliases');

        $this->assertEquals(1, $count);

        $this->assertTrue($response->isOk());

        $strategy = $client->getConnectionStrategy();

        $this->assertInstanceOf(CallbackStrategy::class, $strategy);
    }
}
