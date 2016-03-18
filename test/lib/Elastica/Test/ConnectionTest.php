<?php

namespace Elastica\Test;

use Elastica\Connection;
use Elastica\Request;
use Elastica\Test\Base as BaseTest;

class ConnectionTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testEmptyConstructor()
    {
        $connection = new Connection();
        $this->assertEquals(Connection::DEFAULT_HOST, $connection->getHost());
        $this->assertEquals(Connection::DEFAULT_PORT, $connection->getPort());
        $this->assertEquals(Connection::DEFAULT_TRANSPORT, $connection->getTransport());
        $this->assertInstanceOf('Elastica\Transport\AbstractTransport', $connection->getTransportObject());
        $this->assertEquals(Connection::TIMEOUT, $connection->getTimeout());
        $this->assertEquals(Connection::CONNECT_TIMEOUT, $connection->getConnectTimeout());
        $this->assertEquals(array(), $connection->getConfig());
        $this->assertTrue($connection->isEnabled());
    }

    /**
     * @group unit
     */
    public function testEnabledDisable()
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
     * @expectedException \Elastica\Exception\ConnectionException
     */
    public function testInvalidConnection()
    {
        $connection = new Connection(array('port' => 9999));

        $request = new Request('_stats', Request::GET);
        $request->setConnection($connection);

        // Throws exception because no valid connection
        $request->send();
    }

    /**
     * @group unit
     */
    public function testCreate()
    {
        $connection = Connection::create();
        $this->assertInstanceOf('Elastica\Connection', $connection);

        $connection = Connection::create(array());
        $this->assertInstanceOf('Elastica\Connection', $connection);

        $port = 9999;
        $connection = Connection::create(array('port' => $port));
        $this->assertInstanceOf('Elastica\Connection', $connection);
        $this->assertEquals($port, $connection->getPort());
    }

    /**
     * @group unit
     * @expectedException \Elastica\Exception\InvalidException
     * @expectedException \Elastica\Exception\InvalidException
     */
    public function testCreateInvalid()
    {
        Connection::create('test');
    }

    /**
     * @group unit
     */
    public function testGetConfig()
    {
        $url = 'test';
        $connection = new Connection(array('config' => array('url' => $url)));
        $this->assertTrue($connection->hasConfig('url'));
        $this->assertEquals($url, $connection->getConfig('url'));
    }

    /**
     * @group unit
     */
    public function testGetConfigWithArrayUsedForTransport()
    {
        $connection = new Connection(array('transport' => array('type' => 'Http')));
        $this->assertInstanceOf('Elastica\Transport\Http', $connection->getTransportObject());
    }

    /**
     * @group unit
     * @expectedException Elastica\Exception\InvalidException
     * @expectedExceptionMessage Invalid transport
     */
    public function testGetInvalidConfigWithArrayUsedForTransport()
    {
        $connection = new Connection(array('transport' => array('type' => 'invalidtransport')));
        $connection->getTransportObject();
    }

    /**
     * @group unit
     * @expectedException \Elastica\Exception\InvalidException
     */
    public function testGetConfigInvalidValue()
    {
        $connection = new Connection();
        $connection->getConfig('url');
    }

    /**
     * @group unit
     */
    public function testCompression()
    {
        $connection = new Connection();

        $this->assertFalse($connection->hasCompression());
        $connection->setCompression(true);
        $this->assertTrue($connection->hasCompression());
    }

    /**
     * @group unit
     */
    public function testCompressionDefaultWithClient()
    {
        $client = new \Elastica\Client();
        $connection = $client->getConnection();
        $this->assertFalse($connection->hasCompression());
    }

    /**
     * @group unit
     */
    public function testCompressionEnabledWithClient()
    {
        $client = new \Elastica\Client(array('connections' => array(array('compression' => true))));
        $connection = $client->getConnection();

        $this->assertTrue($connection->hasCompression());
    }

    /**
     * @group unit
     */
    public function testUsernameFromClient()
    {
        $username = 'foo';
        $client = new \Elastica\Client(array('username' => $username));

        $this->assertEquals($username, $client->getConnection()->getUsername('username'));
    }

    /**
     * @group unit
     */
    public function testPasswordFromClient()
    {
        $password = 'bar';
        $client = new \Elastica\Client(array('password' => $password));

        $this->assertEquals($password, $client->getConnection()->getPassword('password'));
    }
}
