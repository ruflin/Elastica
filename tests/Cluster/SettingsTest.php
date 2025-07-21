<?php

declare(strict_types=1);

namespace Elastica\Test\Cluster;

use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastica\Cluster\Settings;
use Elastica\Document;
use Elastica\Test\Base as BaseTest;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class SettingsTest extends BaseTest
{
    #[Group('functional')]
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
        } catch (ClientResponseException $e) {
            $error = \json_decode((string) $e->getResponse()->getBody(), true)['error']['root_cause'][0] ?? null;

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
