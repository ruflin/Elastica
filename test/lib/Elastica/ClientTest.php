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
		$client = new Elastica_Client(array('host' => $host, 'port' => $port));

		$this->assertEquals($host, $client->getHost());
		$this->assertEquals($port, $client->getPort());
	}

	public function testDefaults() {
		$client = new Elastica_Client();

		$this->assertEquals(Elastica_Client::DEFAULT_HOST, 'localhost');
		$this->assertEquals(Elastica_Client::DEFAULT_PORT, 9200);
		$this->assertEquals(Elastica_Client::DEFAULT_TRANSPORT, 'Http');

		$this->assertEquals(Elastica_Client::DEFAULT_HOST, $client->getHost());
		$this->assertEquals(Elastica_Client::DEFAULT_PORT, $client->getPort());
		$this->assertEquals(Elastica_Client::DEFAULT_TRANSPORT, $client->getTransport());
	}

	public function testServersArray() {
		// Creates a new index 'xodoa' and a type 'user' inside this index
		$client = new Elastica_Client(array('servers' => array(array('host' => 'localhost', 'port' => 9200))));
		$index = $client->getIndex('test1');
		$index->create(array(), true);

		$type = $index->getType('user');

		// Adds 1 document to the index
		$doc1 = new Elastica_Document(1,
			array('username' => 'hans', 'test' => array('2', '3', '5'))
		);
		$type->addDocument($doc1);

		// Adds a list of documents with _bulk upload to the index
		$docs = array();
		$docs[] = new Elastica_Document(2,
			array('username' => 'john', 'test' => array('1', '3', '6'))
		);
		$docs[] = new Elastica_Document(3,
			array('username' => 'rolf', 'test' => array('2', '3', '7'))
		);
		$type->addDocuments($docs);

		// Refresh index
		$index->refresh();

		$resultSet = $type->search('rolf');
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
