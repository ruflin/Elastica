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
		$this->assertNotNull($settings->get('index.number_of_replicas'));
		$this->assertNotNull($settings->get('index.number_of_shards'));
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
		$this->assertEquals(2, $settings->get('index.number_of_replicas'));

		$settings->setNumberOfReplicas(3);
		$index->refresh();
		$this->assertEquals(3, $settings->get('index.number_of_replicas'));
	}

	public function testSetRefreshInterval() {
		$indexName = 'test';

		$client = new Elastica_Client();
		$index = $client->getIndex($indexName);
		$index->create(array(), true);


		$settings = $index->getSettings();

		$settings->setRefreshInterval('2s');
		$index->refresh();
		$this->assertEquals('2s', $settings->get('index.refresh_interval'));

		$settings->setRefreshInterval('5s');
		$index->refresh();
		$this->assertEquals('5s', $settings->get('index.refresh_interval'));
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
		$this->assertEquals($interval, $settings->get('index.refresh_interval'));
	}

	public function testSetMergePolicyMergeFactor() {
		$indexName = 'test';

		$client = new Elastica_Client();
		$index = $client->getIndex($indexName);
		$index->create(array(), true);

		$settings = $index->getSettings();

		$settings->setMergePolicyMergeFactor(10);
		$index->refresh();

		$this->assertEquals(10, $settings->get('index.merge.policy.merge_factor'));

		$settings->setMergePolicyMergeFactor(50);
		$index->refresh();
		$this->assertEquals(50, $settings->get('index.merge.policy.merge_factor'));
	}

	public function testGetMergePolicyMergeFactor() {
		$indexName = 'test';

		$client = new Elastica_Client();
		$index = $client->getIndex($indexName);
		$index->create(array(), true);

		$settings = $index->getSettings();

		$this->assertEquals(Elastica_Index_Settings::DEFAULT_MERGE_POLICY_MERGE_FACTOR, $settings->getMergePolicyMergeFactor());

		$interval = '20';
		$settings->setMergePolicyMergeFactor($interval);
		$index->refresh();
		$this->assertEquals($interval, $settings->getMergePolicyMergeFactor());
		$this->assertEquals($interval, $settings->get('index.merge.policy.merge_factor'));
	}
}