<?php

namespace Elastica;

use Elastica\Node\Info;
use Elastica\Node\Stats;

/**
 * Elastica cluster node object
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @link http://www.elasticsearch.org/guide/reference/api/admin-indices-status.html
 */
class Node
{
    /**
     * Client
     *
     * @var \Elastica\Client
     */
    protected $_client = null;

    /**
     * Node name
     *
     * @var string Node name
     */
    protected $_name = '';

    /**
     * Node stats
     *
     * @var \Elastica\Node\Stats Node Stats
     */
    protected $_stats = null;

    /**
     * Node info
     *
     * @var \Elastica\Node\Info Node info
     */
    protected $_info = null;

    /**
     * Create a new node object
     *
     * @param string           $name   Node name
     * @param \Elastica\Client $client Node object
     */
    public function __construct($name, Client $client)
    {
        $this->_name = $name;
        $this->_client = $client;
        $this->refresh();
    }

    /**
     * Get the name of the node
     *
     * @return string Node name
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Returns the current client object
     *
     * @return \Elastica\Client Client
     */
    public function getClient()
    {
        return $this->_client;
    }

    /**
     * Return stats object of the current node
     *
     * @return \Elastica\Node\Stats Node stats
     */
    public function getStats()
    {
        if (!$this->_stats) {
            $this->_stats = new Stats($this);
        }

        return $this->_stats;
    }

    /**
     * Return info object of the current node
     *
     * @return \Elastica\Node\Info Node info object
     */
    public function getInfo()
    {
        if (!$this->_info) {
            $this->_info = new Info($this);
        }

        return $this->_info;
    }

    /**
     * Refreshes all node information
     *
     * This should be called after updating a node to refresh all information
     */
    public function refresh()
    {
        $this->_stats = null;
        $this->_info = null;
    }

    /**
     * Shuts this node down
     *
     * @param  string             $delay OPTIONAL Delay after which node is shut down (default = 1s)
     * @return \Elastica\Response
     * @link http://www.elasticsearch.org/guide/reference/api/admin-cluster-nodes-shutdown.html
     */
    public function shutdown($delay = '1s')
    {
        $path = '_cluster/nodes/'.$this->getName().'/_shutdown?delay='.$delay;

        return $this->_client->request($path, Request::POST);
    }
}
