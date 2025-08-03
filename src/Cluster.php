<?php

declare(strict_types=1);

namespace Elastica;

use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Elastic\Transport\Exception\NoNodeAvailableException;
use Elastica\Cluster\Health;
use Elastica\Cluster\Settings;
use Elastica\Exception\ClientException;

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
    protected Client $_client;

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
     *
     * @throws NoNodeAvailableException if all the hosts are offline
     * @throws ClientResponseException  if the status code of response is 4xx
     * @throws ServerResponseException  if the status code of response is 5xx
     * @throws ClientException
     */
    public function refresh(): void
    {
        $this->_response = $this->_client->toElasticaResponse($this->_client->cluster()->state());
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
