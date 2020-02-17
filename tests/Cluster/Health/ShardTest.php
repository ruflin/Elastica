<?php

namespace Elastica\Test\Cluster\Health;

use Elastica\Cluster\Health\Shard;
use Elastica\Test\Base as BaseTest;

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

    /**
     * @group unit
     */
    public function testGetShardNumber(): void
    {
        $this->assertEquals(2, $this->_shard->getShardNumber());
    }

    /**
     * @group unit
     */
    public function testGetStatus(): void
    {
        $this->assertEquals('red', $this->_shard->getStatus());
    }

    /**
     * @group unit
     */
    public function testisPrimaryActive(): void
    {
        $this->assertTrue($this->_shard->isPrimaryActive());
    }

    /**
     * @group unit
     */
    public function testIsActive(): void
    {
        $this->assertTrue($this->_shard->isActive());
    }

    /**
     * @group unit
     */
    public function testIsRelocating(): void
    {
        $this->assertFalse($this->_shard->isRelocating());
    }

    /**
     * @group unit
     */
    public function testIsInitialized(): void
    {
        $this->assertFalse($this->_shard->isInitialized());
    }

    /**
     * @group unit
     */
    public function testIsUnassigned(): void
    {
        $this->assertTrue($this->_shard->isUnassigned());
    }
}
