<?php

require_once dirname(__FILE__) . '/../../../../bootstrap.php';

class Elastica_Cluster_Health_ShardTest extends Elastica_Test
{
    /**
     * @var Elastica_Cluster_Health_Shard
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

        $this->_shard = new Elastica_Cluster_Health_Shard(2, $shardData);
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
