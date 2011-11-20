<?php

require_once dirname(__FILE__) . '/../../bootstrap.php';


class Elastica_IndexTest extends PHPUnit_Framework_TestCase
{
	public function setUp() {

	}

	public function tearDown() {
	}

	public function testSetLogConfig() {
		$logPath = '/tmp/php.log';
		$client = new Elastica_Client(array('log' => $logPath));
		$this->assertEquals($logPath, $client->getConfig('log'));
	}
	
	public function testEmptyLogConfig() {
		$client = new Elastica_Client();
		$this->assertEmpty($client->getConfig('log'));
	}
	
	public function testDisabledLog() {
		$client = new Elastica_Client();
		$log = new Elastica_Log($client);
		
		$log->log('hello world');
		
		$this->assertEmpty($log->getLastMessage());
	}
	
	public function testGetLastMessage() {
		$client = new Elastica_Client(array('log' => '/tmp/php.log'));
		$log = new Elastica_Log($client);
		$message = 'hello world';
		
		$log->log($message);
	
		$this->assertEquals($message, $log->getLastMessage());
	}
	
	public function testGetLastMessage2() {
		$client = new Elastica_Client(array('log' => true));
		$log = new Elastica_Log($client);
		$message = 'hello world';
	
		$log->log($message);
	
		$this->assertEquals($message, $log->getLastMessage());
	}
}