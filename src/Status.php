<?php

namespace Elastica;

use Elastica\Exception\ResponseException;
use Elasticsearch\Endpoints\Indices\Alias\Get;
use Elasticsearch\Endpoints\Indices\Stats;

/**
 * Elastica general status.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-status.html
 */
class Status
{
    /**
     * Contains all status infos.
     *
     * @var \Elastica\Response Response object
     */
    protected $_response;

    /**
     * Data.
     *
     * @var array Data
     */
    protected $_data;

    /**
     * Client object.
     *
     * @var \Elastica\Client Client object
     */
    protected $_client;

    /**
     * Constructs Status object.
     *
     * @param \Elastica\Client $client Client object
     */
    public function __construct(Client $client)
    {
        $this->_client = $client;
    }

    /**
     * Returns status data.
     *
     * @return array Status data
     */
    public function getData()
    {
        if (null === $this->_data) {
            $this->refresh();
        }

        return $this->_data;
    }

    /**
     * Returns a list of the existing index names.
     *
     * @return array Index names list
     */
    public function getIndexNames()
    {
        $data = $this->getData();

        return \array_keys($data['indices']);
    }

    /**
     * Checks if the given index exists.
     *
     * @param string $name Index name to check
     *
     * @return bool True if index exists
     */
    public function indexExists($name)
    {
        return \in_array($name, $this->getIndexNames());
    }

    /**
     * Checks if the given alias exists.
     *
     * @param string $name Alias name
     *
     * @return bool True if alias exists
     */
    public function aliasExists($name)
    {
        return \count($this->getIndicesWithAlias($name)) > 0;
    }

    /**
     * Returns an array with all indices that the given alias name points to.
     *
     * @param string $alias Alias name
     *
     * @return array|\Elastica\Index[] List of Elastica\Index
     */
    public function getIndicesWithAlias($alias)
    {
        $endpoint = new Get();
        $endpoint->setName($alias);

        $response = null;

        try {
            $response = $this->_client->requestEndpoint($endpoint);
        } catch (ResponseException $e) {
            // 404 means the index alias doesn't exist which means no indexes have it.
            if (404 === $e->getResponse()->getStatus()) {
                return [];
            }
            // If we don't have a 404 then this is still unexpected so rethrow the exception.
            throw $e;
        }
        $indices = [];
        foreach ($response->getData() as $name => $unused) {
            $indices[] = new Index($this->_client, $name);
        }

        return $indices;
    }

    /**
     * Returns response object.
     *
     * @return \Elastica\Response Response object
     */
    public function getResponse()
    {
        if (null === $this->_response) {
            $this->refresh();
        }

        return $this->_response;
    }

    /**
     * Return shards info.
     *
     * @return array Shards info
     */
    public function getShards()
    {
        $data = $this->getData();

        return $data['shards'];
    }

    /**
     * Refresh status object.
     */
    public function refresh(): void
    {
        $this->_response = $this->_client->requestEndpoint(new Stats());
        $this->_data = $this->getResponse()->getData();
    }
}
