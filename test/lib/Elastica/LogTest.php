<?php

require_once dirname(__FILE__) . '/../../bootstrap.php';


class Elastica_LogTest extends Elastica_Test
{
	public function setUp() {

	}

	public function tearDown() {
	}

	public function testSetLogConfigPath() {
		$logPath = '/tmp/php.log';
		$client = new Elastica_Client(array('log' => $logPath));
		$this->assertEquals($logPath, $client->getConfig('log'));
	}
	
	public function testSetLogConfigEnable() {
		$client = new Elastica_Client(array('log' => true));
		$this->assertTrue($client->getConfig('log'));
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
		
		// Set log path temp path as otherwise test fails with output
		$errorLog = ini_get('error_log');
		ini_set('error_log', sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'php.log');
		
		$message = 'hello world';
	
		$log->log($message);
		
		ini_set('error_log', $errorLog);
	
		$this->assertEquals($message, $log->getLastMessage());
	}
}