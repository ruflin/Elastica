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
        $response = $this->_client->request('_cluster/health', Elastica_Request::GET);
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
}

