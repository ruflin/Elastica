<?php
require_once dirname(__FILE__) . '/../../../bootstrap.php';


class Elastica_Transport_MemcacheTest extends PHPUnit_Framework_TestCase
{
	public function setUp() {
	}

	public function tearDown() {
	}

	public function testConstruct() {
		$host = 'localhost';
		$port = 11211;
		$client = new Elastica_Client(array('host' => $host, 'port' => $port, 'transport' => 'Memcache'));

		$this->assertEquals($host, $client->getHost());
		$this->assertEquals($port, $client->getPort());
	}

	public function testExample() {
		// Creates a new index 'xodoa' and a type 'user' inside this index
		$host = 'localhost';
		$port = 11211;
		$client = new Elastica_Client(array('host' => $host, 'port' => $port, 'transport' => 'Memcache'));

		$index = $client->getIndex('elastica_test1');
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
		$this->markTestSkipped('Memcache implementation is not finished yet');
		$resultSet = $type->search('rolf');
	}
}