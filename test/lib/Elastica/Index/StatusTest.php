<?php
require_once dirname(__FILE__) . '/../../../bootstrap.php';

class Elastica_Index_StatusTest extends PHPUnit_Framework_TestCase
{
	public function setUp() { }

	public function tearDown() { }

	public function testGetAliases() {
		$indexName = 'test';
		$aliasName = 'test-alias';

		$client = new Elastica_Client();
		$index = $client->getIndex($indexName);
		$index->create(array(), true);

		$status = new Elastica_Index_Status($index);

		$aliases = $status->getAliases();

		$this->assertTrue(empty($aliases));
		$this->assertInternalType('array', $aliases);

		$index->addAlias($aliasName);
		$status->refresh();

		$aliases = $status->getAliases();

		$this->assertTrue(in_array($aliasName, $aliases));
	}

	public function testHasAlias() {
		$indexName = 'test';
		$aliasName = 'test-alias';

		$client = new Elastica_Client();
		$index = $client->getIndex($indexName);
		$index->create(array(), true);

		$status = new Elastica_Index_Status($index);

		$this->assertFalse($status->hasAlias($aliasName));

		$index->addAlias($aliasName);
		$status->refresh();

		$this->assertTrue($status->hasAlias($aliasName));
	}

	public function testGetSettings() {
		$indexName = 'test';

		$client = new Elastica_Client();
		$index = $client->getIndex($indexName);
		$index->create(array(), true);
		$status = $index->getStatus();

		$settings = $status->getSettings();
		$this->assertInternalType('array', $settings);
		$this->assertTrue(isset($settings['index.number_of_shards']));
	}
}