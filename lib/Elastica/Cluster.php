<?php

namespace Elastica;

use Elastica\Cluster\Health;
use Elastica\Cluster\Settings;
use Elasticsearch\Endpoints\Cluster\State;

/**
 * Cluster information for elasticsearch.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/cluster.html
 */
class Cluster
{
    /**
     * Client.
     *
     * @var Client Client object
     */
    protected $_client;

    /**
     * Cluster state response.
     *
     * @var Response
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
     */
    public function __construct(Client $client)
    {
        $this->_client = $client;
        $this->refresh();
    }

    /**
     * Refreshes all cluster information (state).
     */
    public function refresh(): void
    {
        $this->_response = $this->_client->requestEndpoint(new State());
        $this->_data = $this->getResponse()->getData();
    }

    /**
     * Returns the response object.
     */
    public function getResponse(): Response
    {
        return $this->_response;
    }

    /**
     * Return list of index names.
     *
     * @return string[]
     */
    public function getIndexNames(): array
    {
        return \array_keys($this->_data['metadata']['indices']);
    }

    /**
     * Returns the full state of the cluster.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/cluster-state.html
     */
    public function getState(): array
    {
        return $this->_data;
    }

    /**
     * Returns a list of existing node names.
     *
     * @return string[]
     */
    public function getNodeNames(): array
    {
        $data = $this->getState();
        $nodeNames = [];
        foreach ($data['nodes'] as $node) {
            $nodeNames[] = $node['name'];
        }

        return $nodeNames;
    }

    /**
     * Returns all nodes of the cluster.
     *
     * @return Node[]
     */
    public function getNodes(): array
    {
        $nodes = [];
        $data = $this->getState();

        foreach ($data['nodes'] as $id => $name) {
            $nodes[] = new Node($id, $this->getClient());
        }

        return $nodes;
    }

    public function getClient(): Client
    {
        return $this->_client;
    }

    /**
     * Return Cluster health.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/cluster-health.html
     */
    public function getHealth(): Health
    {
        return new Health($this->getClient());
    }

    /**
     * Return Cluster settings.
     */
    public function getSettings(): Settings
    {
        return new Settings($this->getClient());
    }
}
