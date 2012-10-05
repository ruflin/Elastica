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
        $response = $this->_client->request('_cluster/health', Elastica_Request::GET);
        $health = $response->getData();
        return $health['cluster_name'];
    }
}

