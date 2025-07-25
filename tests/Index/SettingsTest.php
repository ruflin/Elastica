<?php

declare(strict_types=1);

namespace Elastica\Test\Index;

use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastica\Document;
use Elastica\Index\Settings as IndexSettings;
use Elastica\Test\Base as BaseTest;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class SettingsTest extends BaseTest
{
    #[Group('functional')]
    public function testGet(): void
    {
        $indexName = 'elasticatest';

        $client = $this->_getClient();
        $index = $client->getIndex($indexName);
        $index->create([], [
            'recreate' => true,
        ]);
        $index->refresh();
        $settings = $index->getSettings();

        $this->assertIsArray($settings->get());
        $this->assertNotNull($settings->get('number_of_replicas'));
        $this->assertNotNull($settings->get('number_of_shards'));
        $this->assertNull($settings->get('max_result_window'));
        $this->assertNull($settings->get('kjqwerjlqwer'));

        $index->delete();
    }

    #[Group('functional')]
    public function testGetWithDefaultValueIncluded(): void
    {
        $indexName = 'elasticatest';

        $client = $this->_getClient();
        $index = $client->getIndex($indexName);
        $index->create([], [
            'recreate' => true,
        ]);
        $index->refresh();
        $settings = $index->getSettings();

        $this->assertIsArray($settings->get());
        $this->assertEquals(10000, $settings->get('max_result_window', true));
        $this->assertNull($settings->get('kjqwerjlqwer', true));

        $index->delete();
    }

    #[Group('functional')]
    public function testGetWithDefaultValueOverride(): void
    {
        $indexName = 'elasticatest';

        $client = $this->_getClient();

        $index = $client->getIndex($indexName);
        $index->create([
            'settings' => [
                'max_result_window' => 100,
            ],
        ], [
            'recreate' => true,
        ]);

        $index->refresh();
        $settings = $index->getSettings();
        $this->assertIsArray($settings->get());
        $this->assertEquals(100, $settings->get('max_result_window', true));
        $this->assertNull($settings->get('kjqwerjlqwer', true));

        $index->delete();
    }

    #[Group('functional')]
    public function testGetWithAlias(): void
    {
        $indexName = 'elasticatest';
        $aliasName = 'elasticatest_alias';

        $client = $this->_getClient();
        $index = $client->getIndex($indexName);
        $index->create([], [
            'recreate' => true,
        ]);
        $index->refresh();

        $index->addAlias($aliasName);
        $index = $client->getIndex($aliasName);
        $settings = $index->getSettings();

        $this->assertIsArray($settings->get());
        $this->assertNotNull($settings->get('number_of_replicas'));
        $this->assertNotNull($settings->get('number_of_shards'));
        $this->assertNull($settings->get('kjqwerjlqwer'));

        $index = $client->getIndex($indexName);
        $index->delete();
    }

    #[Group('functional')]
    public function testDeleteAliasWithException(): void
    {
        $indexName = 'deletetestaliasexception';
        $aliasName = 'deletetestaliasexception_alias';
        $client = $this->_getClient();
        $index = $client->getIndex($indexName);
        $index->create([], [
            'recreate' => true,
        ]);
        $index->refresh();

        $index->addAlias($aliasName);

        $indexAlias = $client->getIndex($aliasName);

        try {
            $indexAlias->delete();
            $this->fail('Should throw exception because you should delete the concrete index and not the alias');
        } catch (ClientResponseException $e) {
            $error = \json_decode((string) $e->getResponse()->getBody(), true)['error']['root_cause'][0] ?? null;

            $this->assertSame('illegal_argument_exception', $error['type']);
            $this->assertStringContainsString('specify the corresponding concrete indices instead.', $error['reason']);
        }
    }

    #[Group('functional')]
    public function testSetGetNumberOfReplicas(): void
    {
        $index = $this->_createIndex();
        $index->create([], [
            'recreate' => true,
        ]);
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

    #[Group('functional')]
    public function testGetNumberOfReplicas(): void
    {
        $index = $this->_createIndex();
        $index->create([], [
            'recreate' => true,
        ]);

        $settings = $index->getSettings();

        // Test with default number of replicas
        $this->assertEquals(IndexSettings::DEFAULT_NUMBER_OF_REPLICAS, $settings->get('number_of_replicas'));
        $this->assertEquals(IndexSettings::DEFAULT_NUMBER_OF_REPLICAS, $settings->getNumberOfReplicas());

        $index->delete();
    }

    #[Group('functional')]
    public function testGetNumberOfShards(): void
    {
        $index = $this->_createIndex();
        $index->create([], [
            'recreate' => true,
        ]);

        $settings = $index->getSettings();

        // Test with default number of replicas
        $this->assertEquals(1, $settings->get('number_of_shards'));
        $this->assertEquals(1, $settings->getNumberOfShards());

        $index->delete();
    }

    #[Group('functional')]
    public function testGetDefaultNumberOfShards(): void
    {
        $index = $this->_createIndex();
        $index->create([], [
            'recreate' => true,
        ]);
        $index->refresh();

        $settings = $index->getSettings();

        // Test with default number of shards
        $this->assertEquals(IndexSettings::DEFAULT_NUMBER_OF_SHARDS, $settings->get('number_of_shards'));
        $this->assertEquals(IndexSettings::DEFAULT_NUMBER_OF_SHARDS, $settings->getNumberOfShards());

        $index->delete();
    }

    #[Group('functional')]
    public function testSetRefreshInterval(): void
    {
        $index = $this->_createIndex();
        $index->create([], [
            'recreate' => true,
        ]);

        $settings = $index->getSettings();

        $settings->setRefreshInterval('2s');
        $index->refresh();
        $this->assertEquals('2s', $settings->get('refresh_interval'));

        $settings->setRefreshInterval('5s');
        $index->refresh();
        $this->assertEquals('5s', $settings->get('refresh_interval'));

        $index->delete();
    }

    #[Group('functional')]
    public function testGetRefreshInterval(): void
    {
        $index = $this->_createIndex();
        $index->create([], [
            'recreate' => true,
        ]);

        $settings = $index->getSettings();

        $this->assertEquals(IndexSettings::DEFAULT_REFRESH_INTERVAL, $settings->getRefreshInterval());

        $interval = '2s';
        $settings->setRefreshInterval($interval);
        $index->refresh();
        $this->assertEquals($interval, $settings->getRefreshInterval());
        $this->assertEquals($interval, $settings->get('refresh_interval'));

        $index->delete();
    }

    #[Group('functional')]
    public function testSetMergePolicy(): void
    {
        $index = $this->_createIndex();
        $index->create([], [
            'recreate' => true,
        ]);
        // wait for the shards to be allocated
        $this->_waitForAllocation($index);

        $settings = $index->getSettings();

        $settings->setMergePolicy('expunge_deletes_allowed', 15);
        $this->assertEquals(15, $settings->getMergePolicy('expunge_deletes_allowed'));

        $settings->setMergePolicy('expunge_deletes_allowed', 10);
        $this->assertEquals(10, $settings->getMergePolicy('expunge_deletes_allowed'));

        $index->delete();
    }

    #[Group('functional')]
    public function testSetMaxMergeAtOnce(): void
    {
        $index = $this->_createIndex();
        $index->create([], [
            'recreate' => true,
        ]);

        // wait for the shards to be allocated
        $this->_waitForAllocation($index);

        $settings = $index->getSettings();

        $response = $settings->setMergePolicy('max_merge_at_once', 15);
        $this->assertEquals(15, $settings->getMergePolicy('max_merge_at_once'));
        $this->assertTrue($response->isOk());

        $settings->setMergePolicy('max_merge_at_once', 10);
        $this->assertEquals(10, $settings->getMergePolicy('max_merge_at_once'));

        $index->delete();
    }

    #[Group('functional')]
    public function testSetReadOnly(): void
    {
        $index = $this->_createIndex();
        // wait for the shards to be allocated
        $this->_waitForAllocation($index);
        $index->getSettings()->setReadOnly(false);

        // Add document to normal index
        $doc1 = new Document(null, ['hello' => 'world']);
        $doc2 = new Document(null, ['hello' => 'world']);
        $doc3 = new Document(null, ['hello' => 'world']);

        $index->addDocument($doc1);
        $this->assertFalse($index->getSettings()->getReadOnly());

        // Try to add doc to read only index
        $index->getSettings()->setReadOnly(true);
        $this->assertTrue($index->getSettings()->getReadOnly());
        $this->assertTrue($index->exists());

        try {
            $index->addDocument($doc2);
            $this->fail('Should throw exception because of read only');
        } catch (ClientResponseException $e) {
            $error = \json_decode((string) $e->getResponse()->getBody(), true)['error']['root_cause'][0] ?? null;

            $this->assertSame('cluster_block_exception', $error['type']);
            $this->assertStringContainsString('read-only', $error['reason']);
        }

        // Remove read only, add document
        $response = $index->getSettings()->setReadOnly(false);
        $this->assertTrue($response->isOk());

        $index->addDocument($doc3);
        $index->refresh();

        $this->assertEquals(2, $index->count());

        $index->delete();
    }

    #[Group('functional')]
    public function testGetSetBlocksRead(): void
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

    #[Group('functional')]
    public function testGetSetBlocksWrite(): void
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

    #[Group('functional')]
    public function testGetSetBlocksMetadata(): void
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

    #[Group('functional')]
    public function testNotFoundIndex(): void
    {
        $client = $this->_getClient();
        $index = $client->getIndex('not_found_index');

        try {
            $index->getSettings()->get();
            $this->fail('Should throw exception because of index not found');
        } catch (ClientResponseException $e) {
            $error = \json_decode((string) $e->getResponse()->getBody(), true)['error']['root_cause'][0] ?? null;

            $this->assertSame('index_not_found_exception', $error['type']);
        }
    }

    #[Group('functional')]
    public function testSetMultiple(): void
    {
        $index = $this->_createIndex();
        $index->create([], [
            'recreate' => true,
        ]);

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
