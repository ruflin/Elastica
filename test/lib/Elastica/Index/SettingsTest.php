<?php
require_once dirname(__FILE__) . '/../../../bootstrap.php';

class Elastica_Index_SettingsTest extends PHPUnit_Framework_TestCase
{
	public function setUp() {
	}

	public function tearDown() { }

	public function testGet() {
		$indexName = 'elasticatest';

		$client = new Elastica_Client();
		$index = $client->getIndex($indexName);
		$index->create(array(), true);
		$index->refresh();
		$settings = $index->getSettings();

		$this->assertInternalType('array', $settings->get());
		$this->assertNotNull($settings->get('number_of_replicas'));
		$this->assertNotNull($settings->get('number_of_shards'));
		$this->assertNull($settings->get('kjqwerjlqwer'));
	}

	public function testSetNumberOfReplicas() {
		$indexName = 'test';

		$client = new Elastica_Client();
		$index = $client->getIndex($indexName);
		$index->create(array(), true);
		$settings = $index->getSettings();

		$settings->setNumberOfReplicas(2);
		$index->refresh();
		$this->assertEquals(2, $settings->get('number_of_replicas'));

		$settings->setNumberOfReplicas(3);
		$index->refresh();
		$this->assertEquals(3, $settings->get('number_of_replicas'));
	}

	public function testSetRefreshInterval() {
		$indexName = 'test';

		$client = new Elastica_Client();
		$index = $client->getIndex($indexName);
		$index->create(array(), true);


		$settings = $index->getSettings();

		$settings->setRefreshInterval('2s');
		$index->refresh();
		$this->assertEquals('2s', $settings->get('refresh_interval'));

		$settings->setRefreshInterval('5s');
		$index->refresh();
		$this->assertEquals('5s', $settings->get('refresh_interval'));
	}

	public function testGetRefreshInterval() {
		$indexName = 'test';

		$client = new Elastica_Client();
		$index = $client->getIndex($indexName);
		$index->create(array(), true);

		$settings = $index->getSettings();

		$this->assertEquals(Elastica_Index_Settings::DEFAULT_REFRESH_INTERVAL, $settings->getRefreshInterval());

		$interval = '2s';
		$settings->setRefreshInterval($interval);
		$index->refresh();
		$this->assertEquals($interval, $settings->getRefreshInterval());
		$this->assertEquals($interval, $settings->get('refresh_interval'));
	}

	public function testSetMergePolicy() {
		$indexName = 'test';

		$client = new Elastica_Client();
		$index = $client->getIndex($indexName);
		$index->create(array(), true);

		$settings = $index->getSettings();

		$settings->setMergePolicy('expunge_deletes_allowed', 15);
		$this->assertEquals(15, $settings->getMergePolicy('expunge_deletes_allowed'));

		$settings->setMergePolicy('expunge_deletes_allowed', 10);
		$this->assertEquals(10, $settings->getMergePolicy('expunge_deletes_allowed'));
	}

	public function testSetMergeFactor() {
		$indexName = 'test';

		$client = new Elastica_Client();
		$index = $client->getIndex($indexName);
		$index->create(array(), true);

		$settings = $index->getSettings();

		$response = $settings->setMergePolicy('merge_factor', 15);
		$this->assertEquals(15, $settings->getMergePolicy('merge_factor'));
		$this->assertInstanceOf('Elastica_Response', $response);
		$data = $response->getData();
		$this->assertTrue($data['ok']);

		$settings->setMergePolicy('merge_factor', 10);
		$this->assertEquals(10, $settings->getMergePolicy('merge_factor'));
	}

	public function testSetMergePolicyType() {
		$indexName = 'test';

		$client = new Elastica_Client();
		$index = $client->getIndex($indexName);
		$index->create(array(), true);

		$settings = $index->getSettings();

		//$response = $settings->setMergePolicyType('LogByteSizeMergePolicyProvider');
		$response = $settings->setMergePolicyType('log_byte_size');
		$this->assertEquals('log_byte_size', $settings->getMergePolicyType());

		$response = $settings->setMergePolicy('merge_factor', 15);
		$this->assertEquals(15, $settings->getMergePolicy('merge_factor'));
		$this->assertInstanceOf('Elastica_Response', $response);
		$data = $response->getData();
		$this->assertTrue($data['ok']);

	}
}