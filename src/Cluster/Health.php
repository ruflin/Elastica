<?php

declare(strict_types=1);

namespace Elastica\Cluster;

use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Elastic\Transport\Exception\NoNodeAvailableException;
use Elastica\Client;
use Elastica\Cluster\Health\Index;
use Elastica\Exception\ClientException;

/**
 * Elastic cluster health.
 *
 * @author Ray Ward <ray.ward@bigcommerce.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/cluster-health.html
 */
class Health
{
    /**
     * @var Client client object
     */
    protected Client $_client;

    /**
     * @var array the cluster health data
     */
    protected $_data;

    public function __construct(Client $client)
    {
        $this->_client = $client;
        $this->refresh();
    }

    /**
     * Gets the health data.
     */
    public function getData(): array
    {
        return $this->_data;
    }

    /**
     * Refreshes the health data for the cluster.
     */
    public function refresh(): self
    {
        $this->_data = $this->_retrieveHealthData();

        return $this;
    }

    /**
     * Gets the name of the cluster.
     */
    public function getClusterName(): string
    {
        return $this->_data['cluster_name'];
    }

    /**
     * Gets the status of the cluster.
     *
     * @return string green, yellow or red
     */
    public function getStatus(): string
    {
        return $this->_data['status'];
    }

    /**
     * TODO determine the purpose of this.
     */
    public function getTimedOut(): bool
    {
        return $this->_data['timed_out'];
    }

    /**
     * Gets the number of nodes in the cluster.
     */
    public function getNumberOfNodes(): int
    {
        return $this->_data['number_of_nodes'];
    }

    /**
     * Gets the number of data nodes in the cluster.
     */
    public function getNumberOfDataNodes(): int
    {
        return $this->_data['number_of_data_nodes'];
    }

    /**
     * Gets the number of active primary shards.
     */
    public function getActivePrimaryShards(): int
    {
        return $this->_data['active_primary_shards'];
    }

    /**
     * Gets the number of active shards.
     */
    public function getActiveShards(): int
    {
        return $this->_data['active_shards'];
    }

    /**
     * Gets the number of relocating shards.
     */
    public function getRelocatingShards(): int
    {
        return $this->_data['relocating_shards'];
    }

    /**
     * Gets the number of initializing shards.
     */
    public function getInitializingShards(): int
    {
        return $this->_data['initializing_shards'];
    }

    /**
     * Gets the number of unassigned shards.
     */
    public function getUnassignedShards(): int
    {
        return $this->_data['unassigned_shards'];
    }

    /**
     * get the number of delayed unassined shards.
     */
    public function getDelayedUnassignedShards(): int
    {
        return $this->_data['delayed_unassigned_shards'];
    }

    public function getNumberOfPendingTasks(): int
    {
        return $this->_data['number_of_pending_tasks'];
    }

    public function getNumberOfInFlightFetch(): int
    {
        return $this->_data['number_of_in_flight_fetch'];
    }

    public function getTaskMaxWaitingInQueueMillis(): int
    {
        return $this->_data['task_max_waiting_in_queue_millis'];
    }

    public function getActiveShardsPercentAsNumber(): float
    {
        return $this->_data['active_shards_percent_as_number'];
    }

    /**
     * Gets the status of the indices.
     *
     * @return Index[]
     */
    public function getIndices(): array
    {
        $indices = [];
        foreach ($this->_data['indices'] as $indexName => $index) {
            $indices[$indexName] = new Index($indexName, $index);
        }

        return $indices;
    }

    /**
     * Retrieves the health data from the cluster.
     *
     * @throws NoNodeAvailableException if all the hosts are offline
     * @throws ClientResponseException  if the status code of response is 4xx
     * @throws ServerResponseException  if the status code of response is 5xx
     * @throws ClientException
     */
    protected function _retrieveHealthData(): array
    {
        $response = $this->_client->cluster()->health(['level' => 'shards']);

        return $response->asArray();
    }
}
