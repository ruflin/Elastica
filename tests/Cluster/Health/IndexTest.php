<?php

declare(strict_types=1);

namespace Elastica\Test\Cluster\Health;

use Elastica\Cluster\Health\Index;
use Elastica\Cluster\Health\Shard;
use Elastica\Test\Base as BaseTest;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class IndexTest extends BaseTest
{
    /**
     * @var Index
     */
    protected $_index;

    protected function setUp(): void
    {
        parent::setUp();

        $data = [
            'status' => 'yellow',
            'number_of_shards' => 1,
            'number_of_replicas' => 2,
            'active_primary_shards' => 3,
            'active_shards' => 4,
            'relocating_shards' => 5,
            'initializing_shards' => 6,
            'unassigned_shards' => 7,
            'shards' => [
                '0' => [
                    'status' => 'yellow',
                    'primary_active' => false,
                    'active_shards' => 0,
                    'relocating_shards' => 1,
                    'initializing_shards' => 0,
                    'unassigned_shards' => 1,
                ],
                '1' => [
                    'status' => 'yellow',
                    'primary_active' => true,
                    'active_shards' => 1,
                    'relocating_shards' => 0,
                    'initializing_shards' => 0,
                    'unassigned_shards' => 1,
                ],
                '2' => [
                    'status' => 'green',
                    'primary_active' => true,
                    'active_shards' => 1,
                    'relocating_shards' => 0,
                    'initializing_shards' => 0,
                    'unassigned_shards' => 0,
                ],
            ],
        ];

        $this->_index = new Index('test', $data);
    }

    #[Group('unit')]
    public function testGetName(): void
    {
        $this->assertEquals('test', $this->_index->getName());
    }

    #[Group('unit')]
    public function testGetStatus(): void
    {
        $this->assertEquals('yellow', $this->_index->getStatus());
    }

    #[Group('unit')]
    public function testGetNumberOfShards(): void
    {
        $this->assertEquals(1, $this->_index->getNumberOfShards());
    }

    #[Group('unit')]
    public function testGetNumberOfReplicas(): void
    {
        $this->assertEquals(2, $this->_index->getNumberOfReplicas());
    }

    #[Group('unit')]
    public function testGetActivePrimaryShards(): void
    {
        $this->assertEquals(3, $this->_index->getActivePrimaryShards());
    }

    #[Group('unit')]
    public function testGetActiveShards(): void
    {
        $this->assertEquals(4, $this->_index->getActiveShards());
    }

    #[Group('unit')]
    public function testGetRelocatingShards(): void
    {
        $this->assertEquals(5, $this->_index->getRelocatingShards());
    }

    #[Group('unit')]
    public function testGetInitializingShards(): void
    {
        $this->assertEquals(6, $this->_index->getInitializingShards());
    }

    #[Group('unit')]
    public function testGetUnassignedShards(): void
    {
        $this->assertEquals(7, $this->_index->getUnassignedShards());
    }

    #[Group('unit')]
    public function testGetShards(): void
    {
        $shards = $this->_index->getShards();

        $this->assertIsArray($shards);
        $this->assertCount(3, $shards);
        $this->assertContainsOnlyInstancesOf(Shard::class, $shards);
    }
}
