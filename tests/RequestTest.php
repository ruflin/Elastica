<?php

namespace Elastica\Test;

use Elastica\Connection;
use Elastica\Exception\InvalidException;
use Elastica\Request;
use Elastica\Test\Base as BaseTest;
use Symfony\Bridge\PhpUnit\ExpectDeprecationTrait;

/**
 * @internal
 */
class RequestTest extends BaseTest
{
    use ExpectDeprecationTrait;

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
        $this->expectException(InvalidException::class);

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
        $connection->setPort(9200);

        $request = new Request('_stats', Request::GET, [], [], $connection);

        $response = $request->send();

        $this->assertTrue($response->isOk());
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
        $connection->setPort(9200);

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

        $this->assertIsString((string) $request);
    }

    /**
     * @group unit
     * @group legacy
     */
    public function testLegacyToString(): void
    {
        $request = new Request('test');

        $this->expectDeprecation('Since ruflin/elastica 7.1.3: The "Elastica\Request::toString()" method is deprecated, use "__toString()" or cast to string instead. It will be removed in 8.0.');
        $this->assertIsString($request->toString());
    }
}
