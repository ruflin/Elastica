<?php

declare(strict_types=1);

namespace Elastica\Test;

use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastica\Response;
use Elastica\Status;
use Elastica\Test\Base as BaseTest;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class StatusTest extends BaseTest
{
    #[Group('functional')]
    public function testGetResponse(): void
    {
        $index = $this->_createIndex();
        $status = new Status($index->getClient());
        $this->assertInstanceOf(Response::class, $status->getResponse());
    }

    #[Group('functional')]
    public function testGetIndexNames(): void
    {
        $client = $this->_getClient();
        $indexes = [
            '1',
            'test',
        ];

        foreach ($indexes as $name) {
            $client->getIndex($name)->create([], [
                'recreate' => true,
            ]);
        }

        $status = new Status($client);
        $indexNames = $status->getIndexNames();

        $this->assertIsArray($indexNames);
        $this->assertContainsOnly('string', $indexNames);
        $this->assertSame($indexes, \array_intersect($indexes, $indexNames));
    }

    #[Group('functional')]
    public function testIndexExists(): void
    {
        $indexName = 'elastica_test';

        $client = $this->_getClient();
        $index = $client->getIndex($indexName);

        try {
            // Make sure index is deleted first
            $index->delete();
        } catch (ClientResponseException $e) {
        }

        $status = new Status($client);
        $this->assertFalse($status->indexExists($indexName));
        $index->create();

        \usleep(10000);
        $status->refresh();
        $this->assertTrue($status->indexExists($indexName));
    }

    #[Group('functional')]
    public function testAliasExists(): void
    {
        $aliasName = 'elastica_test-alias';

        $index1 = $this->_createIndex();
        $indexName = $index1->getName();

        $status = new Status($index1->getClient());

        foreach ($status->getIndicesWithAlias($aliasName) as $tmpIndex) {
            $tmpIndex->removeAlias($aliasName);
        }

        $this->assertFalse($status->aliasExists($aliasName));

        $index1->addAlias($aliasName);
        $status->refresh();
        $this->assertTrue($status->aliasExists($aliasName));

        $indicesWithAlias = $status->getIndicesWithAlias($aliasName);
        $this->assertEquals([$indexName], \array_map(
            static fn ($index) => $index->getName(),
            $indicesWithAlias
        ));
    }
}
