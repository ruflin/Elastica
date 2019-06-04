<?php

namespace Elastica\Test\Index;

use Elastica\Document;
use Elastica\Exception\ResponseException;
use Elastica\Index;
use Elastica\Index\Settings as IndexSettings;
use Elastica\Response;
use Elastica\Test\Base as BaseTest;

class SettingsTest extends BaseTest
{
    /**
     * @group functional
     */
    public function testGet()
    {
        $indexName = 'elasticatest';

        $client = $this->_getClient();
        $index = $client->getIndex($indexName);
        $index->create([], true);
        $index->refresh();
        $settings = $index->getSettings();

        $this->assertInternalType('array', $settings->get());
        $this->assertNotNull($settings->get('number_of_replicas'));
        $this->assertNotNull($settings->get('number_of_shards'));
        $this->assertNull($settings->get('kjqwerjlqwer'));

        $index->delete();
    }

    /**
     * @group functional
     */
    public function testGetWithAlias()
    {
        $indexName = 'elasticatest';
        $aliasName = 'elasticatest_alias';

        $client = $this->_getClient();
        $index = $client->getIndex($indexName);
        $index->create([], true);
        $index->refresh();

        $index->addAlias($aliasName);
        $index = $client->getIndex($aliasName);
        $settings = $index->getSettings();

        $this->assertInternalType('array', $settings->get());
        $this->assertNotNull($settings->get('number_of_replicas'));
        $this->assertNotNull($settings->get('number_of_shards'));
        $this->assertNull($settings->get('kjqwerjlqwer'));

        $index = $client->getIndex($indexName);
        $index->delete();
    }

    /**
     * @group functional
     */
    public function testDeleteAliasWithException()
    {
        $indexName = 'deletetestaliasexception';
        $aliasName = 'deletetestaliasexception_alias';
        $client = $this->_getClient();
        $index = $client->getIndex($indexName);
        $index->create([], true);
        $index->refresh();

        $index->addAlias($aliasName);

        $indexAlias = $client->getIndex($aliasName);

        try {
            $indexAlias->delete();
            $this->fail('Should throw exception because you should delete the concrete index and not the alias');
        } catch (ResponseException $e) {
            $error = $e->getResponse()->getFullError();

            $this->assertContains('illegal_argument_exception', $error['type']);
            $this->assertContains('specify the corresponding concrete indices instead.', $error['reason']);
        }
    }

    /**
     * @group functional
     */
    public function testSetGetNumberOfReplicas()
    {
        $index = $this->_createIndex();
        $index->create([], true);
        $settings = $index->getSettings();

        // Check for zero replicas
        $settings->setNumberOfReplicas(0);
        $index->refresh();
        $this->assertEquals(0, $settings->get('number_of_replicas'));
        $this->assertEquals(0, $settings->getNumberOfReplicas());

        // Check with 3 replicas
        $settings->setNumberOfReplicas(3);
        $index->refresh();
        $this->assertEquals(3, $settings->get('number_of_replicas'));
        $this->assertEquals(3, $settings->getNumberOfReplicas());

        $index->delete();
    }

    /**
     * @group functional
     */
    public function testGetNumberOfReplicas()
    {
        $index = $this->_createIndex();
        $index->create([], true);

        $settings = $index->getSettings();

        // Test with default number of replicas
        $this->assertEquals(IndexSettings::DEFAULT_NUMBER_OF_REPLICAS, $settings->get('number_of_replicas'));
        $this->assertEquals(IndexSettings::DEFAULT_NUMBER_OF_REPLICAS, $settings->getNumberOfReplicas());

        $index->delete();
    }

    /**
     * @group functional
     */
    public function testGetNumberOfShards()
    {
        $index = $this->_createIndex();
        $index->create([], true);

        $settings = $index->getSettings();

        // Test with default number of replicas
        $this->assertEquals(1, $settings->get('number_of_shards'));
        $this->assertEquals(1, $settings->getNumberOfShards());

        $index->delete();
    }

    /**
     * @group functional
     */
    public function testGetDefaultNumberOfShards()
    {
        $index = $this->_createIndex();
        $index->create([], true);
        $index->refresh();

        $settings = $index->getSettings();

        // Test with default number of shards
        $this->assertEquals(IndexSettings::DEFAULT_NUMBER_OF_SHARDS, $settings->get('number_of_shards'));
        $this->assertEquals(IndexSettings::DEFAULT_NUMBER_OF_SHARDS, $settings->getNumberOfShards());

        $index->delete();
    }

    /**
     * @group functional
     */
    public function testSetRefreshInterval()
    {
        $index = $this->_createIndex();
        $index->create([], true);

        $settings = $index->getSettings();

        $settings->setRefreshInterval('2s');
        $index->refresh();
        $this->assertEquals('2s', $settings->get('refresh_interval'));

        $settings->setRefreshInterval('5s');
        $index->refresh();
        $this->assertEquals('5s', $settings->get('refresh_interval'));

        $index->delete();
    }

    /**
     * @group functional
     */
    public function testGetRefreshInterval()
    {
        $index = $this->_createIndex();
        $index->create([], true);

        $settings = $index->getSettings();

        $this->assertEquals(IndexSettings::DEFAULT_REFRESH_INTERVAL, $settings->getRefreshInterval());

        $interval = '2s';
        $settings->setRefreshInterval($interval);
        $index->refresh();
        $this->assertEquals($interval, $settings->getRefreshInterval());
        $this->assertEquals($interval, $settings->get('refresh_interval'));

        $index->delete();
    }

    /**
     * @group functional
     */
    public function testSetMergePolicy()
    {
        $index = $this->_createIndex();
        $index->create([], true);
        //wait for the shards to be allocated
        $this->_waitForAllocation($index);

        $settings = $index->getSettings();

        $settings->setMergePolicy('expunge_deletes_allowed', 15);
        $this->assertEquals(15, $settings->getMergePolicy('expunge_deletes_allowed'));

        $settings->setMergePolicy('expunge_deletes_allowed', 10);
        $this->assertEquals(10, $settings->getMergePolicy('expunge_deletes_allowed'));

        $index->delete();
    }

    /**
     * @group functional
     */
    public function testSetMaxMergeAtOnce()
    {
        $index = $this->_createIndex();
        $index->create([], true);

        //wait for the shards to be allocated
        $this->_waitForAllocation($index);

        $settings = $index->getSettings();

        $response = $settings->setMergePolicy('max_merge_at_once', 15);
        $this->assertEquals(15, $settings->getMergePolicy('max_merge_at_once'));
        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->isOk());

        $settings->setMergePolicy('max_merge_at_once', 10);
        $this->assertEquals(10, $settings->getMergePolicy('max_merge_at_once'));

        $index->delete();
    }

    /**
     * @group functional
     */
    public function testSetReadOnly()
    {
        $index = $this->_createIndex();
        //wait for the shards to be allocated
        $this->_waitForAllocation($index);
        $index->getSettings()->setReadOnly(false);

        // Add document to normal index
        $doc1 = new Document(null, ['hello' => 'world']);
        $doc2 = new Document(null, ['hello' => 'world']);
        $doc3 = new Document(null, ['hello' => 'world']);

        $type = $index->getType('_doc');
        $type->addDocument($doc1);
        $this->assertFalse($index->getSettings()->getReadOnly());

        // Try to add doc to read only index
        $index->getSettings()->setReadOnly(true);
        $this->assertTrue($index->getSettings()->getReadOnly());
        $this->assertTrue($index->exists());

        try {
            $type->addDocument($doc2);
            $this->fail('Should throw exception because of read only');
        } catch (ResponseException $e) {
            $error = $e->getResponse()->getFullError();

            $this->assertContains('cluster_block_exception', $error['type']);
            $this->assertContains('read-only', $error['reason']);
        }

        // Remove read only, add document
        $response = $index->getSettings()->setReadOnly(false);
        $this->assertTrue($response->isOk());

        $type->addDocument($doc3);
        $index->refresh();

        $this->assertEquals(2, $type->count());

        $index->delete();
    }

    /**
     * @group functional
     */
    public function testGetSetBlocksRead()
    {
        $index = $this->_createIndex();
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

    /**
     * @group functional
     */
    public function testGetSetBlocksWrite()
    {
        $index = $this->_createIndex();
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

    /**
     * @group functional
     */
    public function testGetSetBlocksMetadata()
    {
        $index = $this->_createIndex();
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

    /**
     * @group functional
     */
    public function testNotFoundIndex()
    {
        $client = $this->_getClient();
        $index = $client->getIndex('not_found_index');
        //wait for the shards to be allocated

        try {
            $settings = $index->getSettings()->get();
            $this->fail('Should throw exception because of index not found');
        } catch (ResponseException $e) {
            $error = $e->getResponse()->getFullError();
            $this->assertContains('index_not_found_exception', $error['type']);
        }
    }

    /**
     * @group functional
     */
    public function testSetMultiple()
    {
        $index = $this->_createIndex();
        $index->create([], true);

        $settings = $index->getSettings();

        $index->setSettings([
            'number_of_replicas' => 2,
            'refresh_interval' => '2s',
        ]);

        $index->refresh();
        $this->assertEquals(2, $settings->get('number_of_replicas'));
        $this->assertEquals('2s', $settings->get('refresh_interval'));

        $index->setSettings([
            'number_of_replicas' => 5,
            'refresh_interval' => '5s',
        ]);

        $index->refresh();
        $this->assertEquals(5, $settings->get('number_of_replicas'));
        $this->assertEquals('5s', $settings->get('refresh_interval'));

        $index->delete();
    }
}
