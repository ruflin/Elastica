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
        parent::setUp();

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

    /**
     * @group unit
     */
    public function testGetShardNumber()
    {
        $this->assertEquals(2, $this->_shard->getShardNumber());
    }

    /**
     * @group unit
     */
    public function testGetStatus()
    {
        $this->assertEquals('red', $this->_shard->getStatus());
    }

    /**
     * @group unit
     */
    public function testisPrimaryActive()
    {
        $this->assertTrue($this->_shard->isPrimaryActive());
    }

    /**
     * @group unit
     */
    public function testIsActive()
    {
        $this->assertTrue($this->_shard->isActive());
    }

    /**
     * @group unit
     */
    public function testIsRelocating()
    {
        $this->assertFalse($this->_shard->isRelocating());
    }

    /**
     * @group unit
     */
    public function testIsInitialized()
    {
        $this->assertFalse($this->_shard->isInitialized());
    }

    /**
     * @group unit
     */
    public function testIsUnassigned()
    {
        $this->assertTrue($this->_shard->isUnassigned());
    }
}
