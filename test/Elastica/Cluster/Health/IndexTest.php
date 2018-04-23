<?php
namespace Elastica\Test\Cluster\Health;

use Elastica\Cluster\Health\Index;
use Elastica\Cluster\Health\Shard;
use Elastica\Test\Base as BaseTest;

class IndexTest extends BaseTest
{
    /**
     * @var Index
     */
    protected $_index;

    protected function setUp()
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

    /**
     * @group unit
     */
    public function testGetName()
    {
        $this->assertEquals('test', $this->_index->getName());
    }

    /**
     * @group unit
     */
    public function testGetStatus()
    {
        $this->assertEquals('yellow', $this->_index->getStatus());
    }

    /**
     * @group unit
     */
    public function testGetNumberOfShards()
    {
        $this->assertEquals(1, $this->_index->getNumberOfShards());
    }

    /**
     * @group unit
     */
    public function testGetNumberOfReplicas()
    {
        $this->assertEquals(2, $this->_index->getNumberOfReplicas());
    }

    /**
     * @group unit
     */
    public function testGetActivePrimaryShards()
    {
        $this->assertEquals(3, $this->_index->getActivePrimaryShards());
    }

    /**
     * @group unit
     */
    public function testGetActiveShards()
    {
        $this->assertEquals(4, $this->_index->getActiveShards());
    }

    /**
     * @group unit
     */
    public function testGetRelocatingShards()
    {
        $this->assertEquals(5, $this->_index->getRelocatingShards());
    }

    /**
     * @group unit
     */
    public function testGetInitializingShards()
    {
        $this->assertEquals(6, $this->_index->getInitializingShards());
    }

    /**
     * @group unit
     */
    public function testGetUnassignedShards()
    {
        $this->assertEquals(7, $this->_index->getUnassignedShards());
    }

    /**
     * @group unit
     */
    public function testGetShards()
    {
        $shards = $this->_index->getShards();

        $this->assertInternalType('array', $shards);
        $this->assertCount(3, $shards);

        foreach ($shards as $shard) {
            $this->assertInstanceOf(Shard::class, $shard);
        }
    }
}
