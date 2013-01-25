<?php

namespace Elastica\Test;

use Elastica\Connection;
use Elastica\Request;
use Elastica\Test\Base as BaseTest;

class RequestTest extends BaseTest
{

    public function testConstructor()
    {
        $path = 'test';
        $method = Request::POST;
        $query = array('no' => 'params');
        $data = array('key' => 'value');

        $request = new Request($path, $method, $data, $query);

        $this->assertEquals($path, $request->getPath());
        $this->assertEquals($method, $request->getMethod());
        $this->assertEquals($query, $request->getQuery());
        $this->assertEquals($data, $request->getData());
    }

    /**
     * @expectedException \Elastica\Exception\InvalidException
     */
    public function testInvalidConnection()
    {
        $request = new Request('', Request::GET);
        $request->send();
    }

    public function testSend()
    {
        $connection = new Connection();
        $connection->setHost('localhost');
        $connection->setPort('9200');

        $request = new Request('_status', Request::GET, array(), array(), $connection);

        $response = $request->send();

        $this->assertInstanceOf('Elastica\Response', $response);
    }

    public function testToString()
    {
        $path = 'test';
        $method = Request::POST;
        $query = array('no' => 'params');
        $data = array('key' => 'value');

        $connection = new Connection();
        $connection->setHost('localhost');
        $connection->setPort('9200');

        $request = new Request($path, $method, $data, $query, $connection);

        $data = $request->toArray();

        $this->assertInternalType('array', $data);
        $this->assertArrayHasKey('method', $data);
        $this->assertArrayHasKey('path', $data);
        $this->assertArrayHasKey('query', $data);
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('connection', $data);
        $this->assertEquals($request->getMethod(), $data['method']);
        $this->assertEquals($request->getPath(), $data['path']);
        $this->assertEquals($request->getQuery(), $data['query']);
        $this->assertEquals($request->getData(), $data['data']);
        $this->assertInternalType('array', $data['connection']);
        $this->assertArrayHasKey('host', $data['connection']);
        $this->assertArrayHasKey('port', $data['connection']);
        $this->assertEquals($request->getConnection()->getHost(), $data['connection']['host']);
        $this->assertEquals($request->getConnection()->getPort(), $data['connection']['port']);

        $string = $request->toString();

        $this->assertInternalType('string', $string);

        $string = (string) $request;
        $this->assertInternalType('string', $string);
    }
}
