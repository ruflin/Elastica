<?php

namespace Elastica;
use Elastica\Cluster\Health;
use Elastica\Cluster\Settings;
use Elastica\Exception\NotImplementedException;

/**
 * Cluster informations for elasticsearch
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @link http://www.elasticsearch.org/guide/reference/api/
 */
class Cluster
{
    /**
     * Client
     *
     * @var \Elastica\Client Client object
     */
    protected $_client = null;

    /**
     * Cluster state response.
     *
     * @var \Elastica\Response
     */
    protected $_response;

    /**
     * Cluster state data.
     *
     * @var array
     */
    protected $_data;

    /**
     * Creates a cluster object
     *
     * @param \Elastica\Client $client Connection client object
     */
    public function __construct(Client $client)
    {
        $this->_client = $client;
        $this->refresh();
    }

    /**
     * Refreshes all cluster information (state)
     */
    public function refresh()
    {
        $path = '_cluster/state';
        $this->_response = $this->_client->request($path, Request::GET);
        $this->_data = $this->getResponse()->getData();
    }

    /**
     * Returns the response object
     *
     * @return \Elastica\Response Response object
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * Return list of index names
     *
     * @return array List of index names
     */
    public function getIndexNames()
    {
        $metaData = $this->_data['metadata']['indices'];

        $indices = array();
        foreach ($metaData as $key => $value) {
            $indices[] = $key;
        }

        return $indices;
    }

    /**
     * Returns the full state of the cluster
     *
     * @return array State array
     * @link http://www.elasticsearch.org/guide/reference/api/admin-cluster-state.html
     */
    public function getState()
    {
        return $this->_data;
    }

    /**
     * Returns a list of existing node names
     *
     * @return array List of node names
     */
    public function getNodeNames()
    {
        $data = $this->getState();

        return array_keys($data['routing_nodes']['nodes']);
    }

    /**
     * Returns all nodes of the cluster
     *
     * @return \Elastica\Node[]
     */
    public function getNodes()
    {
        $nodes = array();
        foreach ($this->getNodeNames() as $name) {
            $nodes[] = new Node($name, $this->getClient());
        }

        return $nodes;
    }

    /**
     * Returns the client object
     *
     * @return \Elastica\Client Client object
     */
    public function getClient()
    {
        return $this->_client;
    }

    /**
     * Returns the cluster information (not implemented yet)
     *
     * @param  array                                      $args Additional arguments
     * @throws \Elastica\Exception\NotImplementedException
     * @link http://www.elasticsearch.org/guide/reference/api/admin-cluster-nodes-info.html
     */
    public function getInfo(array $args)
    {
        throw new NotImplementedException('not implemented yet');
    }

    /**
     * Return Cluster health
     *
     * @return \Elastica\Cluster\Health
     * @link http://www.elasticsearch.org/guide/reference/api/admin-cluster-health.html
     */
    public function getHealth()
    {
        return new Health($this->getClient());
    }

    /**
     * Return Cluster settings
     *
     * @return \Elastica\Cluster\Settings
     */
    public function getSettings()
    {
        return new Settings($this->getClient());
    }

    /**
     * Shuts down the complete cluster
     *
     * @param  string            $delay OPTIONAL Seconds to shutdown cluster after (default = 1s)
     * @return \Elastica\Response
     * @link http://www.elasticsearch.org/guide/reference/api/admin-cluster-nodes-shutdown.html
     */
    public function shutdown($delay = '1s')
    {
        $path = '_shutdown?delay=' . $delay;

        return $this->_client->request($path, Request::POST);
    }
}
