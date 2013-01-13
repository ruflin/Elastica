<?php

namespace Elastica\Test;

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
     * @expectedException Elastica\Exception\InvalidException
     */
    public function testInvalidConnection()
    {
        $request = new Request('', Request::GET);
        $request->send();
    }
}
