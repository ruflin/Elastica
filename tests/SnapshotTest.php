<?php

namespace Elastica\Test;

use Elastica\Document;
use Elastica\Exception\NotFoundException;
use Elastica\Index;
use Elastica\Snapshot;

/**
 * @group functional
 *
 * @internal
 */
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

    public function testRegisterRepository(): void
    {
        $location = $this->registerRepository('backup1');

        $response = $this->snapshot->getRepository(self::REPOSITORY_NAME);
        $this->assertEquals($location, $response['settings']['location']);

        // attempt to retrieve a repository which does not exist
        $this->expectException(NotFoundException::class);
        $this->snapshot->getRepository('foobar');
    }

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
        $this->assertCount(1, $data['snapshot']['indices']); // only the specified index should be present
        $this->assertEquals($snapshotName, $data['snapshot']['snapshot']);

        // retrieve data regarding the snapshot
        $response = $this->snapshot->getSnapshot(self::REPOSITORY_NAME, $snapshotName);
        $this->assertContains($this->index->getName(), $response['indices']);

        // delete our test index
        $this->index->delete();

        // restore the index from our snapshot
        $response = $this->snapshot->restoreSnapshot(self::REPOSITORY_NAME, $snapshotName, [], true);
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
        $this->expectException(NotFoundException::class);
        $this->snapshot->getSnapshot(self::REPOSITORY_NAME, $snapshotName);
    }

    private function registerRepository(string $name): string
    {
        $location = self::SNAPSHOT_PATH.'/'.$name;

        $response = $this->snapshot->registerRepository(self::REPOSITORY_NAME, 'fs', ['location' => $location]);
        $this->assertTrue($response->isOk());

        return $location;
    }
}
