<?php

namespace Elastica\Test;

use Elastica\Client;
use Elastica\Connection;
use Elastica\Exception\InvalidException;
use Elastica\Test\Base as BaseTest;

/**
 * @group unit
 *
 * @internal
 */
class ClientTest extends BaseTest
{
    public function testConstruct(): void
    {
        $client = $this->_getClient();
        $this->assertCount(1, $client->getConnections());
    }

    public function testConstructWithDsn(): void
    {
        $client = new Client('https://user:p4ss@foo.com:9200?persistent=false&retryOnConflict=2');
        $this->assertCount(1, $client->getConnections());

        $expected = [
            'host' => 'foo.com',
            'port' => 9200,
            'path' => null,
            'url' => null,
            'proxy' => null,
            'transport' => 'https',
            'persistent' => false,
            'timeout' => null,
            'connections' => [],
            'roundRobin' => false,
            'retryOnConflict' => 2,
            'bigintConversion' => false,
            'username' => 'user',
            'password' => 'p4ss',
            'connectionStrategy' => 'Simple',
        ];

        $this->assertEquals($expected, $client->getConfig());
    }

    public function testConnectionParamsArePreparedForConnectionsOption(): void
    {
        $url = 'https://'.$this->_getHost().':9200';
        $client = $this->_getClient(['connections' => [['url' => $url]]]);
        $connection = $client->getConnection();

        $this->assertEquals($url, $connection->getConfig('url'));
    }

    public function testConnectionParamsArePreparedForServersOption(): void
    {
        $url = 'https://'.$this->_getHost().':9200';
        $client = $this->_getClient(['servers' => [['url' => $url]]]);
        $connection = $client->getConnection();

        $this->assertEquals($url, $connection->getConfig('url'));
    }

    public function testConnectionParamsArePreparedForDefaultOptions(): void
    {
        $url = 'https://'.$this->_getHost().':9200';
        $client = $this->_getClient(['url' => $url]);
        $connection = $client->getConnection();

        $this->assertEquals($url, $connection->getConfig('url'));
    }

    public function testAddDocumentsEmpty(): void
    {
        $this->expectException(InvalidException::class);

        $client = $this->_getClient();
        $client->addDocuments([]);
    }

    public function testConfigValue(): void
    {
        $config = [
            'level1' => [
                'level2' => [
                    'level3' => 'value3',
                ],
                'level21' => 'value21',
            ],
            'level11' => 'value11',
        ];
        $client = $this->_getClient($config);

        $this->assertNull($client->getConfigValue('level12'));
        $this->assertFalse($client->getConfigValue('level12', false));
        $this->assertEquals(10, $client->getConfigValue('level12', 10));

        $this->assertEquals('value11', $client->getConfigValue('level11'));
        $this->assertNotNull($client->getConfigValue('level11'));
        $this->assertNotEquals(false, $client->getConfigValue('level11', false));
        $this->assertNotEquals(10, $client->getConfigValue('level11', 10));

        $this->assertEquals('value3', $client->getConfigValue(['level1', 'level2', 'level3']));
        $this->assertIsArray($client->getConfigValue(['level1', 'level2']));
    }

    public function testAddHeader(): void
    {
        $client = $this->_getClient();

        $client->addHeader('foo', 'bar');
        $this->assertEquals(['foo' => 'bar'], $client->getConfigValue('headers'));
    }

    public function testRemoveHeader(): void
    {
        $client = $this->_getClient();

        $client->addHeader('first', 'first value');
        $client->addHeader('second', 'second value');

        $client->removeHeader('second');
        $this->assertEquals(['first' => 'first value'], $client->getConfigValue('headers'));
    }

    public function testPassBigIntSettingsToConnectionConfig(): void
    {
        $client = new Client(['bigintConversion' => true]);

        $this->assertTrue($client->getConnection()->getConfig('bigintConversion'));
    }

    public function testClientConnectWithConfigSetByMethod(): void
    {
        $client = new Client();
        $client->setConfigValue('host', $this->_getHost());
        $client->setConfigValue('port', $this->_getPort());

        $client->connect();
        $this->assertTrue($client->hasConnection());

        $connection = $client->getConnection();
        $this->assertInstanceOf(Connection::class, $connection);
        $this->assertEquals($this->_getHost(), $connection->getHost());
        $this->assertEquals($this->_getPort(), $connection->getPort());
    }
}
