<?php

namespace Elastica\Test;

use Elastica\Client;
use Elastica\Connection;
use Elastica\Request;
use Elastica\Test\Base as BaseTest;
use Elastica\Transport\AbstractTransport;
use Elastica\Transport\Http;

/**
 * @internal
 */
class ConnectionTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testEmptyConstructor(): void
    {
        $connection = new Connection();
        $this->assertEquals(Connection::DEFAULT_HOST, $connection->getHost());
        $this->assertEquals(Connection::DEFAULT_PORT, $connection->getPort());
        $this->assertEquals(Connection::DEFAULT_TRANSPORT, $connection->getTransport());
        $this->assertInstanceOf(AbstractTransport::class, $connection->getTransportObject());
        $this->assertEquals(Connection::TIMEOUT, $connection->getTimeout());
        $this->assertEquals(Connection::CONNECT_TIMEOUT, $connection->getConnectTimeout());
        $this->assertEquals([], $connection->getConfig());
        $this->assertTrue($connection->isEnabled());
    }

    /**
     * @group unit
     */
    public function testEnabledDisable(): void
    {
        $connection = new Connection();
        $this->assertTrue($connection->isEnabled());
        $connection->setEnabled(false);
        $this->assertFalse($connection->isEnabled());
        $connection->setEnabled(true);
        $this->assertTrue($connection->isEnabled());
    }

    /**
     * @group unit
     */
    public function testInvalidConnection(): void
    {
        $this->expectException(\Elastica\Exception\ConnectionException::class);

        $connection = new Connection(['port' => 9999]);

        $request = new Request('_stats', Request::GET);
        $request->setConnection($connection);

        // Throws exception because no valid connection
        $request->send();
    }

    /**
     * @group unit
     */
    public function testCreate(): void
    {
        $connection = Connection::create();
        $this->assertInstanceOf(Connection::class, $connection);

        $connection = Connection::create([]);
        $this->assertInstanceOf(Connection::class, $connection);

        $port = 9999;
        $connection = Connection::create(['port' => $port]);
        $this->assertInstanceOf(Connection::class, $connection);
        $this->assertEquals($port, $connection->getPort());
    }

    /**
     * @group unit
     */
    public function testCreateInvalid(): void
    {
        $this->expectException(\Elastica\Exception\InvalidException::class);

        Connection::create('test');
    }

    /**
     * @group unit
     */
    public function testGetConfig(): void
    {
        $url = 'test';
        $connection = new Connection(['config' => ['url' => $url]]);
        $this->assertTrue($connection->hasConfig('url'));
        $this->assertEquals($url, $connection->getConfig('url'));
    }

    /**
     * @group unit
     */
    public function testGetConfigWithArrayUsedForTransport(): void
    {
        $connection = new Connection(['transport' => ['type' => 'Http']]);
        $this->assertInstanceOf(Http::class, $connection->getTransportObject());
    }

    /**
     * @group unit
     */
    public function testGetInvalidConfigWithArrayUsedForTransport(): void
    {
        $this->expectException(\Elastica\Exception\InvalidException::class);
        $this->expectExceptionMessage('Invalid transport');

        $connection = new Connection(['transport' => ['type' => 'invalidtransport']]);
        $connection->getTransportObject();
    }

    /**
     * @group unit
     */
    public function testGetConfigInvalidValue(): void
    {
        $this->expectException(\Elastica\Exception\InvalidException::class);

        $connection = new Connection();
        $connection->getConfig('url');
    }

    /**
     * @group unit
     */
    public function testCompression(): void
    {
        $connection = new Connection();

        $this->assertFalse($connection->hasCompression());
        $connection->setCompression(true);
        $this->assertTrue($connection->hasCompression());
    }

    /**
     * @group unit
     */
    public function testCompressionDefaultWithClient(): void
    {
        $client = new Client();
        $connection = $client->getConnection();
        $this->assertFalse($connection->hasCompression());
    }

    /**
     * @group unit
     */
    public function testCompressionEnabledWithClient(): void
    {
        $client = new Client(['connections' => [['compression' => true]]]);
        $connection = $client->getConnection();

        $this->assertTrue($connection->hasCompression());
    }

    /**
     * @group unit
     */
    public function testUsernameFromClient(): void
    {
        $username = 'foo';
        $client = new Client(['username' => $username]);

        $this->assertEquals($username, $client->getConnection()->getUsername('username'));
    }

    /**
     * @group unit
     */
    public function testPasswordFromClient(): void
    {
        $password = 'bar';
        $client = new Client(['password' => $password]);

        $this->assertEquals($password, $client->getConnection()->getPassword('password'));
    }
}
