<?php

namespace Elastica\Test;

use Elastica\Connection;
use Elastica\Request;
use Elastica\Response;
use Elastica\Test\Base as BaseTest;

/**
 * @internal
 */
class RequestTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testConstructor(): void
    {
        $path = 'test';
        $method = Request::POST;
        $query = ['no' => 'params'];
        $data = ['key' => 'value'];

        $request = new Request($path, $method, $data, $query);

        $this->assertEquals($path, $request->getPath());
        $this->assertEquals($method, $request->getMethod());
        $this->assertEquals($query, $request->getQuery());
        $this->assertEquals($data, $request->getData());
    }

    /**
     * @group unit
     */
    public function testInvalidConnection(): void
    {
        $this->expectException(\Elastica\Exception\InvalidException::class);

        $request = new Request('', Request::GET);
        $request->send();
    }

    /**
     * @group functional
     */
    public function testSend(): void
    {
        $connection = new Connection();
        $connection->setHost($this->_getHost());
        $connection->setPort('9200');

        $request = new Request('_stats', Request::GET, [], [], $connection);

        $response = $request->send();

        $this->assertInstanceOf(Response::class, $response);
    }

    /**
     * @group unit
     */
    public function testToString(): void
    {
        $path = 'test';
        $method = Request::POST;
        $query = ['no' => 'params'];
        $data = ['key' => 'value'];

        $connection = new Connection();
        $connection->setHost($this->_getHost());
        $connection->setPort('9200');

        $request = new Request($path, $method, $data, $query, $connection);

        $data = $request->toArray();

        $this->assertIsArray($data);
        $this->assertArrayHasKey('method', $data);
        $this->assertArrayHasKey('path', $data);
        $this->assertArrayHasKey('query', $data);
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('connection', $data);
        $this->assertEquals($request->getMethod(), $data['method']);
        $this->assertEquals($request->getPath(), $data['path']);
        $this->assertEquals($request->getQuery(), $data['query']);
        $this->assertEquals($request->getData(), $data['data']);
        $this->assertIsArray($data['connection']);
        $this->assertArrayHasKey('host', $data['connection']);
        $this->assertArrayHasKey('port', $data['connection']);
        $this->assertEquals($request->getConnection()->getHost(), $data['connection']['host']);
        $this->assertEquals($request->getConnection()->getPort(), $data['connection']['port']);

        $string = $request->toString();

        $this->assertIsString($string);

        $string = (string) $request;
        $this->assertIsString($string);
    }
}
