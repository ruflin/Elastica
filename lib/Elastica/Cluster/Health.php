<?php
namespace Elastica\Cluster;

use Elastica\Client;
use Elastica\Cluster\Health\Index;

/**
 * Elastic cluster health.
 *
 * @author Ray Ward <ray.ward@bigcommerce.com>
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/cluster-health.html
 */
class Health
{
    /**
     * @var \Elastica\Client Client object.
     */
    protected $_client;

    /**
     * @var array The cluster health data.
     */
    protected $_data;

    /**
     * @param \Elastica\Client $client The Elastica client.
     */
    public function __construct(Client $client)
    {
        $this->_client = $client;
        $this->refresh();
    }

    /**
     * Retrieves the health data from the cluster.
     *
     * @return array
     */
    protected function _retrieveHealthData()
    {
        $endpoint = new \Elasticsearch\Endpoints\Cluster\Health();
        $endpoint->setParams(['level' => 'shards']);

        $response = $this->_client->requestEndpoint($endpoint);

        return $response->getData();
    }

    /**
     * Gets the health data.
     *
     * @return array
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     * Refreshes the health data for the cluster.
     *
     * @return $this
     */
    public function refresh()
    {
        $this->_data = $this->_retrieveHealthData();

        return $this;
    }

    /**
     * Gets the name of the cluster.
     *
     * @return string
     */
    public function getClusterName()
    {
        return $this->_data['cluster_name'];
    }

    /**
     * Gets the status of the cluster.
     *
     * @return string green, yellow or red.
     */
    public function getStatus()
    {
        return $this->_data['status'];
    }

    /**
     * TODO determine the purpose of this.
     *
     * @return bool
     */
    public function getTimedOut()
    {
        return $this->_data['timed_out'];
    }

    /**
     * Gets the number of nodes in the cluster.
     *
     * @return int
     */
    public function getNumberOfNodes()
    {
        return $this->_data['number_of_nodes'];
    }

    /**
     * Gets the number of data nodes in the cluster.
     *
     * @return int
     */
    public function getNumberOfDataNodes()
    {
        return $this->_data['number_of_data_nodes'];
    }

    /**
     * Gets the number of active primary shards.
     *
     * @return int
     */
    public function getActivePrimaryShards()
    {
        return $this->_data['active_primary_shards'];
    }

    /**
     * Gets the number of active shards.
     *
     * @return int
     */
    public function getActiveShards()
    {
        return $this->_data['active_shards'];
    }

    /**
     * Gets the number of relocating shards.
     *
     * @return int
     */
    public function getRelocatingShards()
    {
        return $this->_data['relocating_shards'];
    }

    /**
     * Gets the number of initializing shards.
     *
     * @return int
     */
    public function getInitializingShards()
    {
        return $this->_data['initializing_shards'];
    }

    /**
     * Gets the number of unassigned shards.
     *
     * @return int
     */
    public function getUnassignedShards()
    {
        return $this->_data['unassigned_shards'];
    }

    /**
     * get the number of delayed unassined shards.
     *
     * @return int
     */
    public function getDelayedUnassignedShards()
    {
        return $this->_data['delayed_unassigned_shards'];
    }

    /**
     * @return int
     */
    public function getNumberOfPendingTasks()
    {
        return $this->_data['number_of_pending_tasks'];
    }

    /**
     * @return int
     */
    public function getNumberOfInFlightFetch()
    {
        return $this->_data['number_of_in_flight_fetch'];
    }

    /**
     * @return int
     */
    public function getTaskMaxWaitingInQueueMillis()
    {
        return $this->_data['task_max_waiting_in_queue_millis'];
    }

    /**
     * @return int
     */
    public function getActiveShardsPercentAsNumber()
    {
        return $this->_data['active_shards_percent_as_number'];
    }

    /**
     * Gets the status of the indices.
     *
     * @return \Elastica\Cluster\Health\Index[]
     */
    public function getIndices()
    {
        $indices = [];
        foreach ($this->_data['indices'] as $indexName => $index) {
            $indices[$indexName] = new Index($indexName, $index);
        }

        return $indices;
    }
}
