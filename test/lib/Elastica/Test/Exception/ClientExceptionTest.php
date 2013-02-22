<?php

namespace Elastica\Test\Exception;

use Elastica\Client;
use Elastica\Exception\ClientException;
use Elastica\Exception\ConnectionException;
use Elastica\Request;
use Elastica\Test\Base as BaseTest;

class ClientExceptionTest extends BaseTest
{
    public function testConstruct()
    {
        $exceptions = array();
        $exceptions[] = new ConnectionException('message1');
        $exceptions[] = new ConnectionException('message2');
        $exceptions[] = new ConnectionException('message3');

        $clientException = new ClientException('message4', $exceptions);

        $this->assertTrue($clientException->hasConnectionExceptions());
        $this->assertEquals($exceptions, $clientException->getConnectionExceptions());

        $message = $clientException->getMessage();
        $this->assertContains('message4', $message);
        $this->assertContains('message1', $message);
        $this->assertContains('message2', $message);
        $this->assertContains('message3', $message);

        $connectionExceptions = $clientException->getConnectionExceptions();
        $this->assertInternalType('array', $connectionExceptions);
        $this->assertArrayHasKey(0, $connectionExceptions);
        $this->assertInstanceOf('Elastica\\Exception\\ConnectionException', $connectionExceptions[0]);
        $this->assertArrayHasKey(1, $connectionExceptions);
        $this->assertInstanceOf('Elastica\\Exception\\ConnectionException', $connectionExceptions[1]);
    }

    public function testFailedRequest()
    {
        $config = array(
            'connections' => array(
                array(
                    'port' => 10001
                ),
                array(
                    'port' => 10002,
                    'transport' => 'Thrift'
                ),
                array(
                    'port' => 10003,
                    'transport' => 'Https'
                ),
            )
        );

        $client = new Client($config);

        try {
            $client->getStatus();
            $this->fail('Request should fail with client exception');
        } catch (ClientException $e) {
            $this->assertTrue($e->hasConnectionExceptions());
            $message = $e->getMessage();
            $this->assertContains('http://localhost:10001', $message);
            $this->assertContains('thrift://localhost:10002', $message);
            $this->assertContains('https://localhost:10003', $message);
        }

        try {
            $client->getStatus();
            $this->fail('Request should fail with client exception, because there should be no active connection');
        } catch (ClientException $e) {
            $this->assertFalse($e->hasConnectionExceptions());
            $this->assertEmpty($e->getConnectionExceptions());
        }
    }
}