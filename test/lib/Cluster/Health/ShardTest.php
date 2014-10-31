<?php

namespace Elastica\Test\Cluster\Health;

use Elastica\Cluster\Health\Shard as HealthShard;
use Elastica\Test\Base as BaseTest;

class ShardTest extends BaseTest
{
    /**
     * @var \Elastica\Cluster\Health\Shard
     */
    protected $_shard;

    public function setUp()
    {
        $shardData = array(
            'status' => 'red',
            'primary_active' => true,
            'active_shards' => 1,
            'relocating_shards' => 0,
            'initializing_shards' => 0,
            'unassigned_shards' => 1,
        );

        $this->_shard = new HealthShard(2, $shardData);
    }

    public function testGetShardNumber()
    {
        $this->assertEquals(2, $this->_shard->getShardNumber());
    }

    public function testGetStatus()
    {
        $this->assertEquals('red', $this->_shard->getStatus());
    }

    public function testisPrimaryActive()
    {
        $this->assertTrue($this->_shard->isPrimaryActive());
    }

    public function testIsActive()
    {
        $this->assertTrue($this->_shard->isActive());
    }

    public function testIsRelocating()
    {
        $this->assertFalse($this->_shard->isRelocating());
    }

    public function testIsInitialized()
    {
        $this->assertFalse($this->_shard->isInitialized());
    }

    public function testIsUnassigned()
    {
        $this->assertTrue($this->_shard->isUnassigned());
    }
}
