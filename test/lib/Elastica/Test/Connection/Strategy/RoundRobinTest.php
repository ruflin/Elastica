<?php

namespace Elastica\Test\Connection\Strategy;

use Elastica\Client;
use Elastica\Connection;
use Elastica\Connection\Strategy\RoundRobin;
use Elastica\Exception\ConnectionException;
use Elastica\Response;
use Elastica\Test\Base;

/**
 * Description of RoundRobinTest
 *
 * @author chabior
 */
class RoundRobinTest extends Base
{

    public function testConnection()
    {
        $config = array('connectionStrategy' => 'RoundRobin');
        $client = new Client($config);
        $response = $client->request('/_aliases');
        /* @var $response Response */

        $this->_checkResponse($response);

        $this->_checkStrategy($client);
    }

    public function testOldStrategySetted()
    {
        $config = array('roundRobin' => true);
        $client = new Client($config);

        $this->_checkStrategy($client);
    }

    /**
     * @expectedException \Elastica\Exception\ConnectionException
     */
    public function testFailConnection()
    {
        $config = array('connectionStrategy' => 'RoundRobin', 'host' => '255.255.255.0');
        $client = new Client($config);

        $this->_checkStrategy($client);

        $client->request('/_aliases');
    }

    public function testWithOneFailConnection()
    {
        $connections = array(
            new Connection(array('host' => '255.255.255.0')),
            new Connection(array('host' => 'localhost')),
        );

        $count = 0;
        $callback = function ($connection, $exception, $client) use (&$count) {
            ++$count;
        };

        $client = new Client(array('connectionStrategy' => 'RoundRobin'), $callback);
        $client->setConnections($connections);

        $response = $client->request('/_aliases');
        /* @var $response Response */

        $this->_checkResponse($response);

        $this->_checkStrategy($client);

        $this->assertLessThan(count($connections), $count);
    }

    public function testWithNoValidConnection()
    {
        $connections = array(
            new Connection(array('host' => '255.255.255.0', 'timeout' => 2)),
            new Connection(array('host' => '45.45.45.45', 'port' => '80', 'timeout' => 2)),
            new Connection(array('host' => '10.123.213.123', 'timeout' => 2)),
        );

        $count = 0;
        $client = new Client(array('roundRobin' => true), function () use (&$count) {
            ++$count;
        });

        $client->setConnections($connections);

        try {
            $client->request('/_aliases');
            $this->fail('Should throw exception as no connection valid');
        } catch (ConnectionException $e) {
            $this->assertEquals(count($connections), $count);
            $this->_checkStrategy($client);
        }
    }

    protected function _checkStrategy($client)
    {
        $strategy = $client->getConnectionStrategy();

        $this->assertInstanceOf('Elastica\Connection\Strategy\RoundRobin', $strategy);
    }

    protected function _checkResponse($response)
    {
        $this->assertTrue($response->isOk());
    }
}
