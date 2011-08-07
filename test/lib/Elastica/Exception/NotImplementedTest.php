<?php

require_once dirname(__FILE__) . '/../../../bootstrap.php';

class Elastica_Exception_NotImplementedTest extends PHPUnit_Framework_TestCase
{
	public function setUp() {
	}

	public function tearDown() {

	}

	public function testInstance() {
		$code = 4;
		$message = 'Hello world';
		$exception = new Elastica_Exception_NotImplemented($message, $code);

		$this->assertInstanceOf('Elastica_Exception_NotImplemented', $exception);
		$this->assertInstanceOf('Elastica_Exception_Abstract', $exception);
		$this->assertInstanceOf('Exception', $exception);

		$this->assertEquals($message, $exception->getMessage());
		$this->assertEquals($code, $exception->getCode());
	}
}