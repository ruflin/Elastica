<?php
namespace Bonami\Elastica;

use Bonami\Elastica\Cluster\Health;
use Bonami\Elastica\Cluster\Settings;
use Bonami\Elastica\Exception\NotImplementedException;

/**
 * Cluster informations for elasticsearch.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 *
 * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/cluster.html
 */
class Cluster
{
    /**
     * Client.
     *
     * @var \Bonami\Elastica\Client Client object
     */
    protected $_client = null;

    /**
     * Cluster state response.
     *
     * @var \Bonami\Elastica\Response
     */
    protected $_response;

    /**
     * Cluster state data.
     *
     * @var array
     */
    protected $_data;

    /**
     * Creates a cluster object.
     *
     * @param \Bonami\Elastica\Client $client Connection client object
     */
    public function __construct(Client $client)
    {
        $this->_client = $client;
        $this->refresh();
    }

    /**
     * Refreshes all cluster information (state).
     */
    public function refresh()
    {
        $path = '_cluster/state';
        $this->_response = $this->_client->request($path, Request::GET);
        $this->_data = $this->getResponse()->getData();
    }

    /**
     * Returns the response object.
     *
     * @return \Bonami\Elastica\Response Response object
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * Return list of index names.
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
     * Returns the full state of the cluster.
     *
     * @return array State array
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/cluster-state.html
     */
    public function getState()
    {
        return $this->_data;
    }

    /**
     * Returns a list of existing node names.
     *
     * @return array List of node names
     */
    public function getNodeNames()
    {
        $data = $this->getState();
        $nodeNames = array();
        foreach ($data['nodes'] as $node) {
            $nodeNames[] = $node['name'];
        }

        return $nodeNames;
    }

    /**
     * Returns all nodes of the cluster.
     *
     * @return \Bonami\Elastica\Node[]
     */
    public function getNodes()
    {
        $nodes = array();
        $data = $this->getState();

        foreach ($data['nodes'] as $id => $name) {
            $nodes[] = new Node($id, $this->getClient());
        }

        return $nodes;
    }

    /**
     * Returns the client object.
     *
     * @return \Bonami\Elastica\Client Client object
     */
    public function getClient()
    {
        return $this->_client;
    }

    /**
     * Returns the cluster information (not implemented yet).
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/cluster-nodes-info.html
     *
     * @param array $args Additional arguments
     *
     * @throws \Bonami\Elastica\Exception\NotImplementedException
     */
    public function getInfo(array $args)
    {
        throw new NotImplementedException('not implemented yet');
    }

    /**
     * Return Cluster health.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/cluster-health.html
     *
     * @return \Bonami\Elastica\Cluster\Health
     */
    public function getHealth()
    {
        return new Health($this->getClient());
    }

    /**
     * Return Cluster settings.
     *
     * @return \Bonami\Elastica\Cluster\Settings
     */
    public function getSettings()
    {
        return new Settings($this->getClient());
    }

    /**
     * Shuts down the complete cluster.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/cluster-nodes-shutdown.html
     *
     * @param string $delay OPTIONAL Seconds to shutdown cluster after (default = 1s)
     *
     * @return \Bonami\Elastica\Response
     */
    public function shutdown($delay = '1s')
    {
        $path = '_shutdown?delay='.$delay;

        return $this->_client->request($path, Request::POST);
    }
}
