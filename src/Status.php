<?php

declare(strict_types=1);

namespace Elastica;

use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Elastic\Transport\Exception\NoNodeAvailableException;
use Elastica\Exception\ClientException;

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
     * @var Response
     */
    protected $_response;

    /**
     * Data.
     *
     * @var array<string, mixed> Data
     */
    protected $_data;

    protected Client $_client;

    public function __construct(Client $client)
    {
        $this->_client = $client;
    }

    /**
     * Returns status data.
     *
     * @return array<string, mixed> Status data
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
     * @return string[]
     */
    public function getIndexNames()
    {
        $data = $this->getData();

        return \array_map(static fn ($name): string => (string) $name, \array_keys($data['indices']));
    }

    /**
     * Checks if the given index exists.
     *
     * @return bool True if index exists
     */
    public function indexExists(string $name)
    {
        return \in_array($name, $this->getIndexNames(), true);
    }

    /**
     * Checks if the given alias exists.
     *
     * @return bool True if alias exists
     */
    public function aliasExists(string $name)
    {
        return \count($this->getIndicesWithAlias($name)) > 0;
    }

    /**
     * Returns an array with all indices that the given alias name points to.
     *
     * @throws NoNodeAvailableException if all the hosts are offline
     * @throws ClientResponseException  if the status code of response is 4xx
     * @throws ServerResponseException  if the status code of response is 5xx
     * @throws ClientException
     *
     * @return Index[]
     */
    public function getIndicesWithAlias(string $alias)
    {
        $response = null;

        try {
            $response = $this->_client->indices()->getAlias(['name' => $alias]);
        } catch (ClientResponseException $e) {
            // 404 means the index alias doesn't exist which means no indexes have it.
            if (404 === $e->getResponse()->getStatusCode()) {
                return [];
            }
            // If we don't have a 404 then this is still unexpected so rethrow the exception.
            throw $e;
        }
        $indices = [];
        foreach ($response->asArray() as $name => $unused) {
            $indices[] = new Index($this->_client, $name);
        }

        return $indices;
    }

    /**
     * Returns response object.
     */
    public function getResponse(): Response
    {
        if (null === $this->_response) {
            $this->refresh();
        }

        return $this->_response;
    }

    /**
     * Return shards info.
     *
     * @return array<string, mixed> Shards info
     */
    public function getShards()
    {
        $data = $this->getData();

        return $data['shards'];
    }

    /**
     * Refresh status object.
     *
     * @throws NoNodeAvailableException if all the hosts are offline
     * @throws ClientResponseException  if the status code of response is 4xx
     * @throws ServerResponseException  if the status code of response is 5xx
     * @throws ClientException
     */
    public function refresh(): void
    {
        $this->_response = $this->_client->toElasticaResponse(
            $this->_client->indices()->stats()
        );

        $this->_data = $this->getResponse()->getData();
    }
}
