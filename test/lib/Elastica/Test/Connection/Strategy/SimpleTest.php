<?php

namespace Elastica\Test\Connection\Strategy;

use Elastica\Connection;
use Elastica\Exception\ConnectionException;
use Elastica\Test\Base;

/**
 * Description of SimplyTest.
 *
 * @author chabior
 */
class SimpleTest extends Base
{
    /**
     * @var int Number of seconds to wait before timeout is called. Is set low for tests to have fast tests.
     */
    protected $_timeout = 1;

    /**
     * @group functional
     */
    public function testConnection()
    {
        $client = $this->_getClient();
        $response = $client->request('/_aliases');
        /* @var $response \Elastica\Response */

        $this->_checkResponse($response);

        $this->_checkStrategy($client);
    }

    /**
     * @group functional
     * @expectedException \Elastica\Exception\ConnectionException
     */
    public function testFailConnection()
    {
        $config = array('host' => '255.255.255.0', 'timeout' => $this->_timeout);
        $client = $this->_getClient($config);

        $this->_checkStrategy($client);

        $client->request('/_aliases');
    }

    /**
     * @group functional
     */
    public function testWithOneFailConnection()
    {
        $connections = array(
            new Connection(array('host' => '255.255.255.0', 'timeout' => $this->_timeout)),
            new Connection(array('host' => $this->_getHost(), 'timeout' => $this->_timeout)),
        );

        $count = 0;
        $callback = function ($connection, $exception, $client) use (&$count) {
            ++$count;
        };

        $client = $this->_getClient(array(), $callback);
        $client->setConnections($connections);

        $response = $client->request('/_aliases');
        /* @var $response Response */

        $this->_checkResponse($response);

        $this->_checkStrategy($client);

        $this->assertLessThan(count($connections), $count);
    }

    /**
     * @group functional
     */
    public function testWithNoValidConnection()
    {
        $connections = array(
            new Connection(array('host' => '255.255.255.0', 'timeout' => $this->_timeout)),
            new Connection(array('host' => '45.45.45.45', 'port' => '80', 'timeout' => $this->_timeout)),
            new Connection(array('host' => '10.123.213.123', 'timeout' => $this->_timeout)),
        );

        $count = 0;
        $client = $this->_getClient(array(), function () use (&$count) {
            ++$count;
        });

        $client->setConnections($connections);

        try {
            $client->request('/_aliases');
            $this->fail('Should throw exception as no connection valid');
        } catch (ConnectionException $e) {
            $this->assertEquals(count($connections), $count);
        }
    }

    protected function _checkStrategy($client)
    {
        $strategy = $client->getConnectionStrategy();

        $this->assertInstanceOf('Elastica\Connection\Strategy\Simple', $strategy);
    }

    protected function _checkResponse($response)
    {
        $this->assertTrue($response->isOk());
    }
}
