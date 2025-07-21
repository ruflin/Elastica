<?php

declare(strict_types=1);

namespace Elastica\Test;

use Elastica\Document;
use Elastica\Exception\NotFoundException;
use Elastica\Index;
use Elastica\Snapshot;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('functional')]
class SnapshotTest extends Base
{
    private const SNAPSHOT_PATH = '/usr/share/elasticsearch/repository/';
    private const REPOSITORY_NAME = 'repo-name';

    /**
     * @var Snapshot
     */
    protected $snapshot;

    /**
     * @var Index
     */
    protected $index;

    /**
     * @var Document[]
     */
    protected $docs;

    protected function setUp(): void
    {
        parent::setUp();
        $this->snapshot = new Snapshot($this->_getClient());

        $this->index = $this->_createIndex();
        $this->docs = [
            new Document('1', ['city' => 'San Diego']),
            new Document('2', ['city' => 'San Luis Obispo']),
            new Document('3', ['city' => 'San Francisco']),
        ];
        $this->index->addDocuments($this->docs);
        $this->index->refresh();
    }

    #[Group('functional')]
    public function testRegisterRepository(): void
    {
        $location = $this->registerRepository('backup1');

        $response = $this->snapshot->getRepository(self::REPOSITORY_NAME);
        $this->assertEquals($location, $response['settings']['location']);

        // attempt to retrieve a repository which does not exist
        $this->expectException(NotFoundException::class);
        $this->snapshot->getRepository('foobar');

        // delete repository
        $response = $this->snapshot->deleteRepository(self::REPOSITORY_NAME);
        $this->assertTrue($response->isOk());
    }

    #[Group('functional')]
    public function testSnapshotAndRestore(): void
    {
        $this->registerRepository('backup2');

        // create a snapshot of our test index
        $snapshotName = 'test_snapshot_1';
        $response = $this->snapshot->createSnapshot(self::REPOSITORY_NAME, $snapshotName, ['indices' => $this->index->getName()], true);

        // ensure that the snapshot was created properly
        $this->assertTrue($response->isOk());
        $this->assertArrayHasKey('snapshot', $response->getData());
        $data = $response->getData();
        $this->assertContains($this->index->getName(), $data['snapshot']['indices']);

        $this->assertEquals(\in_array($this->index->getName(), $data['snapshot']['indices']), 1);
        $this->assertEquals($snapshotName, $data['snapshot']['snapshot']);

        // retrieve data regarding the snapshot
        $response = $this->snapshot->getSnapshot(self::REPOSITORY_NAME, $snapshotName);
        $this->assertContains($this->index->getName(), $response['indices']);

        // delete our test index
        $this->index->close();
        $this->index->delete();

        // restore the index from our snapshot
        $response = $this->snapshot->restoreSnapshot(self::REPOSITORY_NAME, $snapshotName, [
            'indices' => ['logs-*', $this->index->getName()],
            'include_global_state' => true,
        ], true);
        $this->assertTrue($response->isOk());

        $this->index->refresh();
        $this->index->forcemerge();

        // ensure that the index has been restored
        $count = $this->index->count();
        $this->assertEquals(\count($this->docs), $count);

        // delete the snapshot
        $response = $this->snapshot->deleteSnapshot(self::REPOSITORY_NAME, $snapshotName);
        $this->assertTrue($response->isOk());

        // ensure that the snapshot has been deleted
        $expectedExceptionDeleteSnapshot = null;
        try {
            $this->snapshot->getSnapshot(self::REPOSITORY_NAME, $snapshotName);
        } catch (NotFoundException $e) {
            $expectedExceptionDeleteSnapshot = $e;
        }
        $this->assertInstanceOf(NotFoundException::class, $expectedExceptionDeleteSnapshot);

        // check all snapshots
        $allSnapshots = $this->snapshot->getAllSnapshots(self::REPOSITORY_NAME);
        $this->assertCount(0, $allSnapshots);

        // delete repository
        $response = $this->snapshot->deleteRepository(self::REPOSITORY_NAME);
        $this->assertTrue($response->isOk());

        // check all repositories
        $allRepositories = $this->snapshot->getAllRepositories();
        $this->assertCount(0, $allRepositories);

        // ensure that the repository has been deleted
        $expectedExceptionDeleteRepository = null;
        try {
            $this->snapshot->getRepository(self::REPOSITORY_NAME);
        } catch (NotFoundException $e) {
            $expectedExceptionDeleteRepository = $e;
        }
        $this->assertInstanceOf(NotFoundException::class, $expectedExceptionDeleteRepository);
    }

    private function registerRepository(string $name): string
    {
        $location = self::SNAPSHOT_PATH.'/'.$name;

        $response = $this->snapshot->registerRepository(self::REPOSITORY_NAME, 'fs', ['location' => $location]);
        $this->assertTrue($response->isOk());

        return $location;
    }
}
