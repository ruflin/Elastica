<?php
require_once dirname(__FILE__) . '/../../../bootstrap.php';

class Elastica_Cluster_SettingsTest extends Elastica_Test
{
	public function testSetTransient() {
		$index = $this->_createIndex();
		$settings = new Elastica_Cluster_Settings($index->getClient());

		$settings->setTransient('discovery.zen.minimum_master_nodes', 2);
		$data = $settings->get();
		$this->assertEquals(2, $data['transient']['discovery.zen.minimum_master_nodes']);

		$settings->setTransient('discovery.zen.minimum_master_nodes', 1);
		$data = $settings->get();
		$this->assertEquals(1, $data['transient']['discovery.zen.minimum_master_nodes']);
	}

	public function testSetPersistent() {
		$index = $this->_createIndex();
		$settings = new Elastica_Cluster_Settings($index->getClient());

		$settings->setPersistent('discovery.zen.minimum_master_nodes', 2);
		$data = $settings->get();
		$this->assertEquals(2, $data['persistent']['discovery.zen.minimum_master_nodes']);

		$settings->setPersistent('discovery.zen.minimum_master_nodes', 1);
		$data = $settings->get();
		$this->assertEquals(1, $data['persistent']['discovery.zen.minimum_master_nodes']);
	}

	public function testSetReadOnly() {

		// Create two indices to check that the complete cluster is read only
		$index1 = $this->_createIndex('test1');
		$index2 = $this->_createIndex('test2');

		$settings = new Elastica_Cluster_Settings($index1->getClient());

		$doc = new Elastica_Document(null, array('hello' => 'world'));

		// Check that adding documents work
		$index1->getType('test')->addDocument($doc);
		$index2->getType('test')->addDocument($doc);

		$response = $settings->setReadOnly(true);
		$this->assertFalse($response->hasError());
		$setting = $settings->getTransient('cluster.blocks.read_only');
		$this->assertEquals('true', $setting);

		// Make sure both index are read only
		try {
			$index1->getType('test')->addDocument($doc);
			$this->fail('should throw read only exception');
		} catch(Elastica_Exception_Response $e) {
			$message = $e->getMessage();
			$this->assertContains('ClusterBlockException', $message);
			$this->assertContains('cluster read-only', $message);
		}

		try {
			$index2->getType('test')->addDocument($doc);
			$this->fail('should throw read only exception');
		} catch(Elastica_Exception_Response $e) {
			$message = $e->getMessage();
			$this->assertContains('ClusterBlockException', $message);
			$this->assertContains('cluster read-only', $message);
		}

		$response = $settings->setReadOnly(false);
		$this->assertFalse($response->hasError());
		$setting = $settings->getTransient('cluster.blocks.read_only');
		$this->assertEquals('false', $setting);


		// Check that adding documents works again
		$index1->getType('test')->addDocument($doc);
		$index2->getType('test')->addDocument($doc);

		$index1->refresh();
		$index2->refresh();

		// 2 docs should be in each index
		$this->assertEquals(2, $index1->count());
		$this->assertEquals(2, $index2->count());
	}
}
