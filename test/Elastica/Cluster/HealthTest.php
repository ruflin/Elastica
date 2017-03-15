<?php
namespace Elastica\Test\Cluster;

use Elastica\Cluster\Health;
use Elastica\Cluster\Health\Index;
use Elastica\Test\Base as BaseTest;

class HealthTest extends BaseTest
{
    /**
     * @var Health
     */
    protected $_health;

    protected function setUp()
    {
        parent::setUp();

        $data = [
            'cluster_name' => 'test_cluster',
            'status' => 'green',
            'timed_out' => false,
            'number_of_nodes' => 10,
            'number_of_data_nodes' => 8,
            'active_primary_shards' => 3,
            'active_shards' => 4,
            'relocating_shards' => 2,
            'initializing_shards' => 7,
            'unassigned_shards' => 5,
            'indices' => [
                'index_one' => [
                ],
                'index_two' => [
                ],
            ],
        ];

        $health = $this
            ->getMockBuilder(Health::class)
            ->setConstructorArgs([$this->_getClient()])
            ->setMethods(['_retrieveHealthData'])
            ->getMock();

        $health
            ->expects($this->any())
            ->method('_retrieveHealthData')
            ->will($this->returnValue($data));

        // need to explicitly refresh because the mocking won't refresh the data in the constructor
        $health->refresh();

        $this->_health = $health;
    }

    /**
     * @group unit
     */
    public function testGetClusterName()
    {
        $this->assertEquals('test_cluster', $this->_health->getClusterName());
    }

    /**
     * @group unit
     */
    public function testGetStatus()
    {
        $this->assertEquals('green', $this->_health->getStatus());
    }

    /**
     * @group unit
     */
    public function testGetTimedOut()
    {
        $this->assertFalse($this->_health->getTimedOut());
    }

    /**
     * @group unit
     */
    public function testGetNumberOfNodes()
    {
        $this->assertEquals(10, $this->_health->getNumberOfNodes());
    }

    /**
     * @group unit
     */
    public function testGetNumberOfDataNodes()
    {
        $this->assertEquals(8, $this->_health->getNumberOfDataNodes());
    }

    /**
     * @group unit
     */
    public function testGetActivePrimaryShards()
    {
        $this->assertEquals(3, $this->_health->getActivePrimaryShards());
    }

    /**
     * @group unit
     */
    public function testGetActiveShards()
    {
        $this->assertEquals(4, $this->_health->getActiveShards());
    }

    /**
     * @group unit
     */
    public function testGetRelocatingShards()
    {
        $this->assertEquals(2, $this->_health->getRelocatingShards());
    }

    /**
     * @group unit
     */
    public function testGetInitializingShards()
    {
        $this->assertEquals(7, $this->_health->getInitializingShards());
    }

    /**
     * @group unit
     */
    public function testGetUnassignedShards()
    {
        $this->assertEquals(5, $this->_health->getUnassignedShards());
    }

    /**
     * @group unit
     */
    public function testGetIndices()
    {
        $indices = $this->_health->getIndices();

        $this->assertInternalType('array', $indices);
        $this->assertEquals(2, count($indices));
        $this->assertArrayHasKey('index_one', $indices);
        $this->assertArrayHasKey('index_two', $indices);

        foreach ($indices as $index) {
            $this->assertInstanceOf(Index::class, $index);
        }
    }
}
