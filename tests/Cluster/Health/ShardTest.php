<?php

declare(strict_types=1);

namespace Elastica\Test\Cluster\Health;

use Elastica\Cluster\Health\Shard;
use Elastica\Test\Base as BaseTest;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class ShardTest extends BaseTest
{
    /**
     * @var Shard
     */
    protected $_shard;

    protected function setUp(): void
    {
        parent::setUp();

        $shardData = [
            'status' => 'red',
            'primary_active' => true,
            'active_shards' => 1,
            'relocating_shards' => 0,
            'initializing_shards' => 0,
            'unassigned_shards' => 1,
        ];

        $this->_shard = new Shard(2, $shardData);
    }

    #[Group('unit')]
    public function testGetShardNumber(): void
    {
        $this->assertEquals(2, $this->_shard->getShardNumber());
    }

    #[Group('unit')]
    public function testGetStatus(): void
    {
        $this->assertEquals('red', $this->_shard->getStatus());
    }

    #[Group('unit')]
    public function testisPrimaryActive(): void
    {
        $this->assertTrue($this->_shard->isPrimaryActive());
    }

    #[Group('unit')]
    public function testIsActive(): void
    {
        $this->assertTrue($this->_shard->isActive());
    }

    #[Group('unit')]
    public function testIsRelocating(): void
    {
        $this->assertFalse($this->_shard->isRelocating());
    }

    #[Group('unit')]
    public function testIsInitialized(): void
    {
        $this->assertFalse($this->_shard->isInitialized());
    }

    #[Group('unit')]
    public function testIsUnassigned(): void
    {
        $this->assertTrue($this->_shard->isUnassigned());
    }
}
