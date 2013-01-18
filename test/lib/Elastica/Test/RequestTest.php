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

        $string = $request->toString();

        $expected = 'curl -XPOST \'http://localhost:9200/test?no=params\' -d \'{"key":"value"}\'';
        $this->assertEquals($expected, $string);

        $string = (string) $request;
        $this->assertEquals($expected, $string);
    }
}
