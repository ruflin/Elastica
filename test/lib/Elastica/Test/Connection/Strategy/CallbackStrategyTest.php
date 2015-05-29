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
            $count++;
        };

        $strategy = new CallbackStrategy($callback);
        $strategy->getConnection(array());

        $this->assertEquals(1, $count);
    }

    /**
     * @group unit
     */
    public function testIsValid()
    {
        $callback = function () {};

        $isValid = CallbackStrategy::isValid($callback);

        $this->assertTrue($isValid);
    }

    /**
     * @group unit
     */
    public function testFailIsValid()
    {
        $callback = new \stdClass();

        $isValid = CallbackStrategy::isValid($callback);

        $this->assertFalse($isValid);
    }

    /**
     * @group functional
     */
    public function testConnection()
    {
        $count = 0;

        $config = array('connectionStrategy' => function ($connections) use (&$count) {
            ++$count;

            return current($connections);
       });

        $client = $this->_getClient($config);
        $response = $client->request('/_aliases');

        $this->assertEquals(1, $count);

        $this->assertTrue($response->isOk());

        $strategy = $client->getConnectionStrategy();

        $this->assertInstanceOf('Elastica\Connection\Strategy\CallbackStrategy', $strategy);
    }
}
