<?php

namespace Elastica\Test\Connection\Strategy;

use Elastica\Client;
use Elastica\Connection;
use Elastica\Connection\Strategy\Simple;
use Elastica\Exception\ConnectionException;
use Elastica\Response;
use Elastica\Test\Base;

/**
 * Description of SimplyTest.
 *
 * @author chabior
 *
 * @internal
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
    public function testConnection(): void
    {
        $client = $this->_getClient();
        $response = $client->request('_aliases');

        $this->_checkResponse($response);

        $this->_checkStrategy($client);
    }

    /**
     * @group functional
     */
    public function testFailConnection(): void
    {
        $this->expectException(\Elastica\Exception\ConnectionException::class);

        $config = ['host' => '255.255.255.0', 'timeout' => $this->_timeout];
        $client = $this->_getClient($config);

        $this->_checkStrategy($client);

        $client->request('_aliases');
    }

    /**
     * @group functional
     */
    public function testWithOneFailConnection(): void
    {
        $connections = [
            new Connection(['host' => '255.255.255.0', 'timeout' => $this->_timeout]),
            new Connection(['host' => $this->_getHost(), 'timeout' => $this->_timeout]),
        ];

        $count = 0;
        $callback = function ($connection, $exception, $client) use (&$count): void {
            ++$count;
        };

        $client = $this->_getClient([], $callback);
        $client->setConnections($connections);

        $response = $client->request('_aliases');
        /* @var $response Response */

        $this->_checkResponse($response);

        $this->_checkStrategy($client);

        $this->assertLessThan(\count($connections), $count);
    }

    /**
     * @group functional
     */
    public function testWithNoValidConnection(): void
    {
        $connections = [
            new Connection(['host' => '255.255.255.0', 'timeout' => $this->_timeout]),
            new Connection(['host' => '45.45.45.45', 'port' => '80', 'timeout' => $this->_timeout]),
            new Connection(['host' => '10.123.213.123', 'timeout' => $this->_timeout]),
        ];

        $count = 0;
        $client = $this->_getClient([], function () use (&$count): void {
            ++$count;
        });

        $client->setConnections($connections);

        try {
            $client->request('_aliases');
            $this->fail('Should throw exception as no connection valid');
        } catch (ConnectionException $e) {
            $this->assertEquals(\count($connections), $count);
        }
    }

    protected function _checkStrategy(Client $client): void
    {
        $strategy = $client->getConnectionStrategy();

        $this->assertInstanceOf(Simple::class, $strategy);
    }

    protected function _checkResponse(Response $response): void
    {
        $this->assertTrue($response->isOk());
    }
}
