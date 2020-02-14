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
        $index1 = $this->_createIndex();

        $doc1 = new Document(null, ['hello' => 'world']);
        $doc2 = new Document(null, ['hello' => 'world']);
        $doc3 = new Document(null, ['hello' => 'world']);
        $doc4 = new Document(null, ['hello' => 'world']);
        $doc5 = new Document(null, ['hello' => 'world']);
        $doc6 = new Document(null, ['hello' => 'world']);

        // Check that adding documents work
        $index1->addDocument($doc1);

        $response = $settings->setReadOnly(true);
        $this->assertFalse($response->hasError());
        $setting = $settings->getTransient('cluster.blocks.read_only');
        $this->assertEquals('true', $setting);

        // Make sure both index are read only
        try {
            $index1->addDocument($doc3);
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
        $index1->addDocument($doc5);

        $index1->refresh();

        // 2 docs should be in each index
        $this->assertEquals(2, $index1->count());
    }
}
