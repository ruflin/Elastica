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
     * Gets the health data.
     *
     * @return array
     */
    private function _getHealthData()
    {
        $response = $this->_client->request('_cluster/health', Elastica_Request::GET);
        return $response->getData();
    }

    /**
     * @param Elastica_Client $client The Elastica client.
     */
    public function __construct(Elastica_Client $client)
    {
        $this->_client = $client;
    }

    /**
     * Gets the name of the cluster.
     *
     * @return string
     */
    public function getClusterName()
    {
        $health = $this->_getHealthData();
        return $health['cluster_name'];
    }

    /**
     * Gets the status of the cluster.
     *
     * @return string green, yellow or red.
     */
    public function getStatus()
    {
        $health = $this->_getHealthData();
        return $health['status'];
    }
}

