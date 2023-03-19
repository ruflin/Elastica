<?php

namespace Elastica\Test\Cluster;

use Elastica\Cluster\Settings;
use Elastica\Document;
use Elastica\Exception\ResponseException;
use Elastica\Test\Base as BaseTest;

/**
 * @internal
 */
class SettingsTest extends BaseTest
{
    /**
     * @group functional
     */
    public function testSetTransient(): void
    {
        if (\version_compare($_SERVER['ES_VERSION'], '7.0.0', '>=')) {
            $this->markTestSkipped('discovery.zen.minimum_master_nodes is deprecated, ignored in 7.x and removed in 8.x, see: https://www.elastic.co/guide/en/elasticsearch/reference/master/migrating-8.0.html#breaking-changes-8.0');
        }

        $index = $this->_createIndex();

        if (\count($index->getClient()->getCluster()->getNodes()) < 2) {
            $this->markTestSkipped('At least two master nodes have to be running for this test');
        }

        $settings = new Settings($index->getClient());

        $settings->setTransient('discovery.zen.minimum_master_nodes', 2);
        $data = $settings->get();
        $this->assertEquals(2, $data['transient']['discovery']['zen']['minimum_master_nodes']);

        $settings->setTransient('discovery.zen.minimum_master_nodes', 1);
        $data = $settings->get();
        $this->assertEquals(1, $data['transient']['discovery']['zen']['minimum_master_nodes']);
    }

    /**
     * @group functional
     */
    public function testSetPersistent(): void
    {
        if (\version_compare($_SERVER['ES_VERSION'], '7.0.0', '>=')) {
            $this->markTestSkipped('discovery.zen.minimum_master_nodes is deprecated, ignored in 7.x and removed in 8.x, see: https://www.elastic.co/guide/en/elasticsearch/reference/master/migrating-8.0.html#breaking-changes-8.0');
        }

        $index = $this->_createIndex();

        if (\count($index->getClient()->getCluster()->getNodes()) < 2) {
            $this->markTestSkipped('At least two master nodes have to be running for this test');
        }

        $settings = new Settings($index->getClient());

        $settings->setPersistent('discovery.zen.minimum_master_nodes', 2);
        $data = $settings->get();
        $this->assertEquals(2, $data['persistent']['discovery']['zen']['minimum_master_nodes']);

        $settings->setPersistent('discovery.zen.minimum_master_nodes', 1);
        $data = $settings->get();
        $this->assertEquals(1, $data['persistent']['discovery']['zen']['minimum_master_nodes']);
    }

    /**
     * @group functional
     */
    public function testSetReadOnly(): void
    {
        // Create two indices to check that the complete cluster is read only
        $settings = new Settings($this->_getClient());
        $settings->setReadOnly(false);
        $index = $this->_createIndex();

        $doc1 = new Document(null, ['hello' => 'world']);
        $doc2 = new Document(null, ['hello' => 'world']);

        // Check that adding documents work
        $index->addDocument($doc1);

        $response = $settings->setReadOnly(true);
        $this->assertFalse($response->hasError());
        $setting = $settings->getTransient('cluster.blocks.read_only');
        $this->assertEquals('true', $setting);

        // Make sure both index are read only
        try {
            $index->addDocument($doc2);
            $this->fail('should throw read only exception');
        } catch (ResponseException $e) {
            $error = $e->getResponse()->getFullError();
            $this->assertSame('cluster_block_exception', $error['type']);
            $this->assertStringContainsString('cluster read-only', $error['reason']);
        }

        $response = $settings->setReadOnly(false);
        $this->assertFalse($response->hasError());
        $setting = $settings->getTransient('cluster.blocks.read_only');
        $this->assertEquals('false', $setting);

        // Check that adding documents works again
        $index->addDocument($doc2);

        $index->refresh();

        // 2 docs should be in each index
        $this->assertEquals(2, $index->count());
    }
}
