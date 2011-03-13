<?php
require_once dirname(__FILE__) . '/../../bootstrap.php';

class Elastica_StatusTest extends PHPUnit_Framework_TestCase
{
	public function setUp() {

	}

	public function tearDown() {
	}

	public function testGetResponse() {
		$client = new Elastica_Client();
		$client->getIndex('test');

		$status = new Elastica_Status($client);

		$this->assertInstanceOf('Elastica_Response', $status->getResponse());
	}

	public function testGetIndexStatuses() {
		$indexName = 'test';
		$client = new Elastica_Client();
		$index = $client->getIndex($indexName);
		$index->create(array(), true);

		$status = new Elastica_Status($client);
		$statuses = $status->getIndexStatuses();

		$this->assertInternalType('array', $statuses);

		foreach($statuses as $indexStatus) {
			$this->assertInstanceOf('Elastica_Status_Index', $indexStatus);
		}
	}

	public function testGetIndexNames() {
		$indexName = 'test';
		$client = new Elastica_Client();
		$index = $client->getIndex($indexName);
		$index->create(array(), true);

		$status = new Elastica_Status($client);
		$names = $status->getIndexNames();

		$this->assertInternalType('array', $names);
		$this->assertTrue(in_array($indexName, $names));

		foreach($names as $name) {
			$this->assertInternalType('string', $name);
		}
	}

	public function testIndexExists() {
		$indexName = 'test';
		$aliasName = 'test-alias';

		$client = new Elastica_Client();
		$index = $client->getIndex($indexName);

		try {
			// Make sure index is deleted first
			$index->delete();
		} catch(Elastica_Exception_Response $e) { }

		$status = new Elastica_Status($client);
		$this->assertFalse($status->indexExists($indexName));
		$index->create();

		$status->refresh();
		$this->assertTrue($status->indexExists($indexName));
	}

	public function testAliasExists() {
		$indexName = 'test';
		$aliasName = 'test-alias';

		$client = new Elastica_Client();
		$index1 = $client->getIndex($indexName);

		$index1->create(array(), true);

		$status = new Elastica_Status($client);

		foreach($status->getIndicesWithAlias($aliasName) as $tmpIndex) {
			$tmpIndex->removeAlias($aliasName);
		}

		$this->assertFalse($status->aliasExists($aliasName));

		$index1->addAlias($aliasName);
		$status->refresh();
		$this->assertTrue($status->aliasExists($aliasName));
	}
}