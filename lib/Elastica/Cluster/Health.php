<?php

/**
 * Elastic cluster health.
 *
 * @package Elastica
 * @author Ray Ward <ray.ward@bigcommerce.com>
 * @link http://www.elasticsearch.org/guide/reference/api/admin-cluster-health.html
 */
class Elastica_Cluster_Health
{
    /**
     * Elastica client.
     *
     * @var Elastica_Client Client object
     */
    protected $_client = null;

    /**
     * The cluster health data.
     *
     * @var array
     */
    protected $_healthData = null;

    /**
     * @param Elastica_Client $client The Elastica client.
     */
    public function __construct(Elastica_Client $client)
    {
        $this->_client = $client;
        $this->refresh();
    }

    /**
     * Gets the health data.
     *
     * @return array
     */
    public function getData()
    {
        return $this->_healthData;
    }

    /**
     * Refreshes the health data for the cluster.
     */
    public function refresh()
    {
        $path = '_cluster/health?level=shards';
        $response = $this->_client->request($path, Elastica_Request::GET);
        $this->_healthData = $response->getData();
    }

    /**
     * Gets the name of the cluster.
     *
     * @return string
     */
    public function getClusterName()
    {
        return $this->_healthData['cluster_name'];
    }

    /**
     * Gets the status of the cluster.
     *
     * @return string green, yellow or red.
     */
    public function getStatus()
    {
        return $this->_healthData['status'];
    }

    /**
     * TODO determine the purpose of this.
     *
     * @return bool
     */
    public function getTimedOut()
    {
        return $this->_healthData['timed_out'];
    }

    /**
     * Gets the number of nodes in the cluster.
     *
     * @return int
     */
    public function getNumberOfNodes()
    {
        return $this->_healthData['number_of_nodes'];
    }

    /**
     * Gets the number of data nodes in the cluster.
     *
     * @return int
     */
    public function getNumberOfDataNodes()
    {
        return $this->_healthData['number_of_data_nodes'];
    }

    /**
     * Gets the number of active primary shards.
     *
     * @return int
     */
    public function getActivePrimaryShards()
    {
        return $this->_healthData['active_primary_shards'];
    }

    /**
     * Gets the number of active shards.
     *
     * @return int
     */
    public function getActiveShards()
    {
        return $this->_healthData['active_shards'];
    }

    /**
     * Gets the number of relocating shards.
     *
     * @return int
     */
    public function getRelocatingShards()
    {
        return $this->_healthData['relocating_shards'];
    }

    /**
     * Gets the number of initializing shards.
     *
     * @return int
     */
    public function getInitializingShards()
    {
        return $this->_healthData['initializing_shards'];
    }

    /**
     * Gets the number of unassigned shards.
     *
     * @return int
     */
    public function getUnassignedShards()
    {
        return $this->_healthData['unassigned_shards'];
    }

    /**
     * Gets the status of the indices.
     *
     * @return array Array of Elastica_Cluster_Health_Index objects.
     */
    public function getIndices()
    {
        $indices = array();
        foreach ($this->_healthData['indices'] as $indexName => $index) {
            $indices[] = new Elastica_Cluster_Health_Index($indexName, $index);
        }

        return $indices;
    }
}

