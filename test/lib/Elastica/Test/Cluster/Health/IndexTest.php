<?php

namespace Elastica\Test\Cluster\Health;

use Elastica\Cluster\Health\Index as HealthIndex;
use Elastica\Test\Base as BaseTest;

class IndexTest extends BaseTest
{
    /**
     * @var \Elastica\Cluster\Health\Index
     */
    protected $_index;

    public function setUp()
    {
        $data = array(
            "status" => "yellow",
            "number_of_shards" => 1,
            "number_of_replicas" => 2,
            "active_primary_shards" => 3,
            "active_shards" => 4,
            "relocating_shards" => 5,
            "initializing_shards" => 6,
            "unassigned_shards" => 7,
            "shards" => array(
                "0" => array(
                    "status" => "yellow",
                    "primary_active" => false,
                    "active_shards" => 0,
                    "relocating_shards" => 1,
                    "initializing_shards" => 0,
                    "unassigned_shards" => 1,
                ),
                "1" => array(
                    "status" => "yellow",
                    "primary_active" => true,
                    "active_shards" => 1,
                    "relocating_shards" => 0,
                    "initializing_shards" => 0,
                    "unassigned_shards" => 1,
                ),
                "2" => array(
                    "status" => "green",
                    "primary_active" => true,
                    "active_shards" => 1,
                    "relocating_shards" => 0,
                    "initializing_shards" => 0,
                    "unassigned_shards" => 0,
                ),
            ),
        );

        $this->_index = new HealthIndex('test', $data);
    }

    public function testGetName()
    {
        $this->assertEquals('test', $this->_index->getName());
    }

    public function testGetStatus()
    {
        $this->assertEquals('yellow', $this->_index->getStatus());
    }

    public function testGetNumberOfShards()
    {
        $this->assertEquals(1, $this->_index->getNumberOfShards());
    }

    public function testGetNumberOfReplicas()
    {
        $this->assertEquals(2, $this->_index->getNumberOfReplicas());
    }

    public function testGetActivePrimaryShards()
    {
        $this->assertEquals(3, $this->_index->getActivePrimaryShards());
    }

    public function testGetActiveShards()
    {
        $this->assertEquals(4, $this->_index->getActiveShards());
    }

    public function testGetRelocatingShards()
    {
        $this->assertEquals(5, $this->_index->getRelocatingShards());
    }

    public function testGetInitializingShards()
    {
        $this->assertEquals(6, $this->_index->getInitializingShards());
    }

    public function testGetUnassignedShards()
    {
        $this->assertEquals(7, $this->_index->getUnassignedShards());
    }

    public function testGetShards()
    {
        $shards = $this->_index->getShards();

        $this->assertInternalType('array', $shards);
        $this->assertEquals(3, count($shards));

        foreach ($shards as $shard) {
            $this->assertInstanceOf('Elastica\Cluster\Health\Shard', $shard);
        }
    }
}
