<?php

declare(strict_types=1);

namespace Elastica\Test\Cluster;

use Elastica\Cluster\Health;
use Elastica\Cluster\Health\Index;
use Elastica\Test\Base as BaseTest;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class HealthTest extends BaseTest
{
    /**
     * @var Health
     */
    protected $_health;

    protected function setUp(): void
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
            'delayed_unassigned_shards' => 4,
            'number_of_pending_tasks' => 1,
            'number_of_in_flight_fetch' => 2,
            'task_max_waiting_in_queue_millis' => 634,
            'active_shards_percent_as_number' => 50.0,

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
            ->onlyMethods(['_retrieveHealthData'])
            ->getMock()
        ;

        $health
            ->method('_retrieveHealthData')
            ->willReturn($data)
        ;

        // need to explicitly refresh because the mocking won't refresh the data in the constructor
        $health->refresh();

        $this->_health = $health;
    }

    #[Group('unit')]
    public function testGetClusterName(): void
    {
        $this->assertEquals('test_cluster', $this->_health->getClusterName());
    }

    #[Group('unit')]
    public function testGetStatus(): void
    {
        $this->assertEquals('green', $this->_health->getStatus());
    }

    #[Group('unit')]
    public function testGetTimedOut(): void
    {
        $this->assertFalse($this->_health->getTimedOut());
    }

    #[Group('unit')]
    public function testGetNumberOfNodes(): void
    {
        $this->assertEquals(10, $this->_health->getNumberOfNodes());
    }

    #[Group('unit')]
    public function testGetNumberOfDataNodes(): void
    {
        $this->assertEquals(8, $this->_health->getNumberOfDataNodes());
    }

    #[Group('unit')]
    public function testGetActivePrimaryShards(): void
    {
        $this->assertEquals(3, $this->_health->getActivePrimaryShards());
    }

    #[Group('unit')]
    public function testGetActiveShards(): void
    {
        $this->assertEquals(4, $this->_health->getActiveShards());
    }

    #[Group('unit')]
    public function testGetRelocatingShards(): void
    {
        $this->assertEquals(2, $this->_health->getRelocatingShards());
    }

    #[Group('unit')]
    public function testGetInitializingShards(): void
    {
        $this->assertEquals(7, $this->_health->getInitializingShards());
    }

    #[Group('unit')]
    public function testGetUnassignedShards(): void
    {
        $this->assertEquals(5, $this->_health->getUnassignedShards());
    }

    #[Group('unit')]
    public function testGetDelayedUnassignedShards(): void
    {
        $this->assertEquals(4, $this->_health->getDelayedUnassignedShards());
    }

    #[Group('unit')]
    public function testNumberOfPendingTasks(): void
    {
        $this->assertEquals(1, $this->_health->getNumberOfPendingTasks());
    }

    #[Group('unit')]
    public function testNumberOfInFlightFetch(): void
    {
        $this->assertEquals(2, $this->_health->getNumberOfInFlightFetch());
    }

    #[Group('unit')]
    public function testTaskMaxWaitingInQueueMillis(): void
    {
        $this->assertEquals(634, $this->_health->getTaskMaxWaitingInQueueMillis());
    }

    #[Group('unit')]
    public function testActiveShardsPercentAsNumber(): void
    {
        $this->assertEquals(50.0, $this->_health->getActiveShardsPercentAsNumber());
    }

    #[Group('unit')]
    public function testGetIndices(): void
    {
        $indices = $this->_health->getIndices();

        $this->assertIsArray($indices);
        $this->assertCount(2, $indices);
        $this->assertArrayHasKey('index_one', $indices);
        $this->assertArrayHasKey('index_two', $indices);
        $this->assertContainsOnlyInstancesOf(Index::class, $indices);
    }
}
