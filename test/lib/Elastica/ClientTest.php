<?php
require_once dirname(__FILE__) . '/../../bootstrap.php';


class Elastica_ClientTest extends PHPUnit_Framework_TestCase
{
    public function setUp() {     
    }
    
    public function tearDown() {
    }
	
	public function testConstruct() {
		$host = 'ruflin.com';
		$port = 9300;
		$client = new Elastica_Client($host, $port);
		
		$this->assertEquals($host, $client->getHost());
		$this->assertEquals($port, $client->getPort());
	}
		
	public function testDefaults() {
		$client = new Elastica_Client();
		
		$this->assertEquals(Elastica_Client::DEFAULT_HOST, 'localhost');
		$this->assertEquals(Elastica_Client::DEFAULT_PORT, 9200);
		
		$this->assertEquals(Elastica_Client::DEFAULT_HOST, $client->getHost());
		$this->assertEquals(Elastica_Client::DEFAULT_PORT, $client->getPort());
	}
	
	public function testBulk() {
		$client = new Elastica_Client();
		
		$params = array(
			array('index' => array('_index' => 'test', '_type' => 'user', '_id' => '1')),
			array('user' => array('name' => 'hans')),
			array('index' => array('_index' => 'test', '_type' => 'user', '_id' => '2')),
			array('user' => array('name' => 'peter')),
		);
	
		$client->bulk($params);
	}
	
	public function testOptimizeAll() {
		$client = new Elastica_Client();
		$response = $client->optimizeAll();
		
		$this->assertFalse($response->hasError());
	}
	
	public function testAddDocumentsEmpty() {
		$client = new Elastica_Client();
		try {
			$client->addDocuments(array());
			$this->fail('Should throw exception');
		} catch(Elastica_Exception_Invalid $e) {
			$this->assertTrue(true);
		}
	}
}
