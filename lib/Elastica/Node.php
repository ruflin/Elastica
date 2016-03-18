<?php

namespace Elastica;

use Elastica\Node\Info;
use Elastica\Node\Stats;

/**
 * Elastica cluster node object.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class Node
{
    /**
     * Client.
     *
     * @var \Elastica\Client
     */
    protected $_client = null;

    /**
     * @var string Unique node id
     */
    protected $_id = '';

    /**
     * Node name.
     *
     * @var string Node name
     */
    protected $_name = '';

    /**
     * Node stats.
     *
     * @var \Elastica\Node\Stats Node Stats
     */
    protected $_stats = null;

    /**
     * Node info.
     *
     * @var \Elastica\Node\Info Node info
     */
    protected $_info = null;

    /**
     * Create a new node object.
     *
     * @param string           $id     Node id or name
     * @param \Elastica\Client $client Node object
     */
    public function __construct($id, Client $client)
    {
        $this->_client = $client;
        $this->setId($id);
    }

    /**
     * @return string Unique node id. Can also be name if id not exists.
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * @param string $id Node id
     *
     * @return $this Refreshed object
     */
    public function setId($id)
    {
        $this->_id = $id;

        return $this->refresh();
    }

    /**
     * Get the name of the node.
     *
     * @return string Node name
     */
    public function getName()
    {
        if (empty($this->_name)) {
            $this->_name = $this->getInfo()->getName();
        }

        return $this->_name;
    }

    /**
     * Returns the current client object.
     *
     * @return \Elastica\Client Client
     */
    public function getClient()
    {
        return $this->_client;
    }

    /**
     * Return stats object of the current node.
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/cluster-nodes-stats.html
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
     * Return info object of the current node.
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/cluster-nodes-info.html
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
     * Refreshes all node information.
     *
     * This should be called after updating a node to refresh all information
     */
    public function refresh()
    {
        $this->_stats = null;
        $this->_info = null;
    }

    /**
     * Shuts this node down.
     *
     * @param string $delay OPTIONAL Delay after which node is shut down (default = 1s)
     *
     * @return \Elastica\Response
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/cluster-nodes-shutdown.html
     */
    public function shutdown($delay = '1s')
    {
        $path = '_cluster/nodes/'.$this->getId().'/_shutdown?delay='.$delay;

        return $this->_client->request($path, Request::POST);
    }
}
