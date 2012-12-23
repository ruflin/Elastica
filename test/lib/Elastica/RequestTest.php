<?php

require_once dirname(__FILE__) . '/../../bootstrap.php';

class Elastica_RequestTest extends Elastica_Test
{

	public function testConstructor() {

		$path = 'test';
		$method = Elastica_Request::POST;
		$query = array('no' => 'params');
		$data = array('key' => 'value');

		$request = new Elastica_Request($path, $method, $data, $query);

		$this->assertEquals($path, $request->getPath());
		$this->assertEquals($method, $request->getMethod());
		$this->assertEquals($query, $request->getQuery());
		$this->assertEquals($data, $request->getData());
	}

	/**
	 * @expectedException Elastica_Exception_Invalid
	 */
	public function testInvalidConnection()
    {
		$request = new Elastica_Request('', Elastica_Request::GET);
		$request->send();
	}
}