<?php

namespace Elastica\Test\Index;

use Elastica\Document;
use Elastica\Exception\ResponseException;
use Elastica\Index;
use Elastica\Index\Settings as IndexSettings;
use Elastica\Test\Base as BaseTest;

class SettingsTest extends BaseTest
{
    public function testGet()
    {
        $indexName = 'elasticatest';

        $client = $this->_getClient();
        $index = $client->getIndex($indexName);
        $index->create(array(), true);
        $index->refresh();
        $settings = $index->getSettings();

        $this->assertInternalType('array', $settings->get());
        $this->assertNotNull($settings->get('number_of_replicas'));
        $this->assertNotNull($settings->get('number_of_shards'));
        $this->assertNull($settings->get('kjqwerjlqwer'));
    }

    public function testSetNumberOfReplicas()
    {
        $indexName = 'test';

        $client = $this->_getClient();
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

    public function testSetRefreshInterval()
    {
        $indexName = 'test';

        $client = $this->_getClient();
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

    public function testGetRefreshInterval()
    {
        $indexName = 'test';

        $client = $this->_getClient();
        $index = $client->getIndex($indexName);
        $index->create(array(), true);

        $settings = $index->getSettings();

        $this->assertEquals(IndexSettings::DEFAULT_REFRESH_INTERVAL, $settings->getRefreshInterval());

        $interval = '2s';
        $settings->setRefreshInterval($interval);
        $index->refresh();
        $this->assertEquals($interval, $settings->getRefreshInterval());
        $this->assertEquals($interval, $settings->get('refresh_interval'));
    }

    public function testSetMergePolicy()
    {
        $indexName = 'test';

        $client = $this->_getClient();
        $index = $client->getIndex($indexName);
        $index->create(array(), true);

        $settings = $index->getSettings();

        $settings->setMergePolicy('expunge_deletes_allowed', 15);
        $this->assertEquals(15, $settings->getMergePolicy('expunge_deletes_allowed'));

        $settings->setMergePolicy('expunge_deletes_allowed', 10);
        $this->assertEquals(10, $settings->getMergePolicy('expunge_deletes_allowed'));
    }

    public function testSetMergeFactor()
    {
        $indexName = 'test';

        $client = $this->_getClient();
        $index = $client->getIndex($indexName);
        $index->create(array(), true);

        $settings = $index->getSettings();

        $response = $settings->setMergePolicy('merge_factor', 15);
        $this->assertEquals(15, $settings->getMergePolicy('merge_factor'));
        $this->assertInstanceOf('Elastica\Response', $response);
        $data = $response->getData();
        $this->assertTrue($data['ok']);

        $settings->setMergePolicy('merge_factor', 10);
        $this->assertEquals(10, $settings->getMergePolicy('merge_factor'));
    }

    public function testSetMergePolicyType()
    {
        $indexName = 'test';

        $client = $this->_getClient();
        $index = $client->getIndex($indexName);
        $index->create(array(), true);

        $settings = $index->getSettings();

        //$response = $settings->setMergePolicyType('LogByteSizeMergePolicyProvider');
        $response = $settings->setMergePolicyType('log_byte_size');
        $this->assertEquals('log_byte_size', $settings->getMergePolicyType());

        $response = $settings->setMergePolicy('merge_factor', 15);
        $this->assertEquals(15, $settings->getMergePolicy('merge_factor'));
        $this->assertInstanceOf('Elastica\Response', $response);
        $data = $response->getData();
        $this->assertTrue($data['ok']);
    }

    public function testSetReadOnly()
    {
        $client = $this->_getClient();
        $index = new Index($client, 'elastica_test');
        $index->getSettings()->setReadOnly(false);

        $index = $this->_createIndex();

        // Add document to normal index
        $doc1 = new Document(null, array('hello' => 'world'));
        $doc2 = new Document(null, array('hello' => 'world'));
        $doc3 = new Document(null, array('hello' => 'world'));

        $type = $index->getType('test');
        $type->addDocument($doc1);
        $this->assertFalse((bool) $index->getSettings()->get('blocks.read_only'));

        // Try to add doc to read only index
        $index->getSettings()->setReadOnly(true);
        $this->assertTrue((bool) $index->getSettings()->get('blocks.read_only'));

        try {
            $type->addDocument($doc2);
            $this->fail('Should throw exception because of read only');
        } catch (ResponseException $e) {
            $message = $e->getMessage();
            $this->assertContains('ClusterBlockException', $message);
            $this->assertContains('index read-only', $message);
        }

        // Remove read only, add document
        $response = $index->getSettings()->setReadOnly(false);
        $this->assertTrue($response->isOk());

        $type->addDocument($doc3);
        $index->refresh();

        $this->assertEquals(2, $type->count());
    }


    public function testGetSetBlocksRead()
    {
        $client = $this->_getClient();
        $index = $client->getIndex('elastica-test');
        $index->create();
        $index->refresh();
        $settings = $index->getSettings();

        $this->assertFalse($settings->getBlocksRead());

        $settings->setBlocksRead(true);
        $this->assertTrue($settings->getBlocksRead());

        $settings->setBlocksRead(false);
        $this->assertFalse($settings->getBlocksRead());

        $settings->setBlocksRead();
        $this->assertTrue($settings->getBlocksRead());

        $index->delete();
    }

    public function testGetSetBlocksWrite()
    {
        $client = $this->_getClient();
        $index = $client->getIndex('elastica-test');
        $index->create();
        $index->refresh();
        $settings = $index->getSettings();

        $this->assertFalse($settings->getBlocksWrite());

        $settings->setBlocksWrite(true);
        $this->assertTrue($settings->getBlocksWrite());

        $settings->setBlocksWrite(false);
        $this->assertFalse($settings->getBlocksWrite());

        $settings->setBlocksWrite();
        $this->assertTrue($settings->getBlocksWrite());

        $index->delete();
    }

    public function testGetSetBlocksMetadata()
    {
        $client = $this->_getClient();
        $index = $client->getIndex('elastica-test');
        $index->create();
        $index->refresh();
        $settings = $index->getSettings();

        $this->assertFalse($settings->getBlocksMetadata());

        $settings->setBlocksMetadata(true);
        $this->assertTrue($settings->getBlocksMetadata());

        $settings->setBlocksMetadata(false);
        $this->assertFalse($settings->getBlocksMetadata());

        $settings->setBlocksMetadata();
        $this->assertTrue($settings->getBlocksMetadata());

        $settings->setBlocksMetadata(false); // Cannot delete index otherwise
        $index->delete();
    }
}
