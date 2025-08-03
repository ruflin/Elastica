<?php

declare(strict_types=1);

namespace Elastica;

use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\MissingParameterException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Elastic\Transport\Exception\NoNodeAvailableException;
use Elastica\Exception\ClientException;
use Elastica\Exception\NotFoundException;

/**
 * Class Snapshot.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/modules-snapshots.html
 */
class Snapshot
{
    protected Client $_client;

    public function __construct(Client $client)
    {
        $this->_client = $client;
    }

    /**
     * Register a snapshot repository.
     *
     * @param string $name     the name of the repository
     * @param string $type     the repository type ("fs" for file system)
     * @param array  $settings Additional repository settings. If type "fs" is used, the "location" setting must be provided.
     *
     * @throws MissingParameterException if a required parameter is missing
     * @throws NoNodeAvailableException  if all the hosts are offline
     * @throws ClientResponseException   if the status code of response is 4xx
     * @throws ServerResponseException   if the status code of response is 5xx
     * @throws ClientException
     */
    public function registerRepository($name, $type, $settings = []): Response
    {
        $params = [
            'repository' => $name,
            'body' => [
                'type' => $type,
                'settings' => $settings,
            ],
        ];

        return $this->_client->toElasticaResponse(
            $this->_client->snapshot()->createRepository($params)
        );
    }

    /**
     * Retrieve a repository record by name.
     *
     * @param string $name the name of the desired repository
     *
     * @throws NoNodeAvailableException if all the hosts are offline
     * @throws ClientResponseException  if the status code of response is 4xx
     * @throws ServerResponseException  if the status code of response is 5xx
     * @throws NotFoundException
     * @throws ClientException
     *
     * @return array
     */
    public function getRepository($name)
    {
        try {
            $response = $this->_client->snapshot()->getRepository(['repository' => $name]);
        } catch (ClientResponseException $e) {
            if (404 === $e->getResponse()->getStatusCode()) {
                throw new NotFoundException("Repository '".$name."' does not exist.");
            }
            throw $e;
        }
        $data = $response->asArray();

        return $data[$name];
    }

    /**
     * Retrieve all repository records.
     *
     * @throws NoNodeAvailableException if all the hosts are offline
     * @throws ClientResponseException  if the status code of response is 4xx
     * @throws ServerResponseException  if the status code of response is 5xx
     * @throws ClientException
     *
     * @return array
     */
    public function getAllRepositories()
    {
        return $this->_client->snapshot()->getRepository()->asArray();
    }

    /**
     * Create a new snapshot.
     *
     * @param string $repository        the name of the repository in which this snapshot should be stored
     * @param string $name              the name of this snapshot
     * @param array  $options           optional settings for this snapshot
     * @param bool   $waitForCompletion if true, the request will not return until the snapshot operation is complete
     *
     * @throws MissingParameterException if a required parameter is missing
     * @throws NoNodeAvailableException  if all the hosts are offline
     * @throws ClientResponseException   if the status code of response is 4xx
     * @throws ServerResponseException   if the status code of response is 5xx
     * @throws ClientException
     */
    public function createSnapshot($repository, $name, $options = [], $waitForCompletion = false): Response
    {
        $params = [
            'repository' => $repository,
            'snapshot' => $name,
            'wait_for_completion' => $waitForCompletion,
        ];

        return $this->_client->toElasticaResponse(
            $this->_client->snapshot()->create(\array_merge($params, $options))
        );
    }

    /**
     * Retrieve data regarding a specific snapshot.
     *
     * @param string $repository the name of the repository from which to retrieve the snapshot
     * @param string $name       the name of the desired snapshot
     *
     * @throws MissingParameterException if a required parameter is missing
     * @throws NoNodeAvailableException  if all the hosts are offline
     * @throws ClientResponseException   if the status code of response is 4xx
     * @throws ServerResponseException   if the status code of response is 5xx
     * @throws NotFoundException
     * @throws ClientException
     *
     * @return array
     */
    public function getSnapshot($repository, $name)
    {
        try {
            $response = $this->_client->snapshot()->get(['repository' => $repository, 'snapshot' => $name]);
        } catch (ClientResponseException $e) {
            if (404 === $e->getResponse()->getStatusCode()) {
                throw new NotFoundException("Snapshot '".$name."' does not exist in repository '".$repository."'.");
            }
            throw $e;
        }
        $data = $response->asArray();

        return $data['snapshots'][0];
    }

    /**
     * Delete a repository.
     *
     * @param string $repository the name of the repository from which to retrieve the snapshot
     *
     * @throws MissingParameterException if a required parameter is missing
     * @throws NoNodeAvailableException  if all the hosts are offline
     * @throws ClientResponseException   if the status code of response is 4xx
     * @throws ServerResponseException   if the status code of response is 5xx
     * @throws NotFoundException
     * @throws ClientException
     */
    public function deleteRepository($repository): Response
    {
        return $this->_client->toElasticaResponse(
            $this->_client->snapshot()->deleteRepository(['repository' => $repository])
        );
    }

    /**
     * Retrieve data regarding all snapshots in the given repository.
     *
     * @param string $repository the repository name
     *
     * @throws NoNodeAvailableException if all the hosts are offline
     * @throws ClientResponseException  if the status code of response is 4xx
     * @throws ServerResponseException  if the status code of response is 5xx
     * @throws ClientException
     *
     * @return array
     */
    public function getAllSnapshots($repository)
    {
        $data = $this->_client->snapshot()->get(['repository' => $repository, 'snapshot' => '*'])->asArray();

        return $data['snapshots'] ?? [];
    }

    /**
     * Delete a snapshot.
     *
     * @param string $repository the repository in which the snapshot resides
     * @param string $name       the name of the snapshot to be deleted
     *
     * @throws MissingParameterException if a required parameter is missing
     * @throws NoNodeAvailableException  if all the hosts are offline
     * @throws ClientResponseException   if the status code of response is 4xx
     * @throws ServerResponseException   if the status code of response is 5xx
     * @throws ClientException
     */
    public function deleteSnapshot($repository, $name): Response
    {
        return $this->_client->toElasticaResponse(
            $this->_client->snapshot()->delete(['repository' => $repository, 'snapshot' => $name])
        );
    }

    /**
     * Restore a snapshot.
     *
     * @param string $repository        the name of the repository
     * @param string $name              the name of the snapshot
     * @param array  $options           options for the restore operation
     * @param bool   $waitForCompletion if true, the request will not return until the restore operation is complete
     *
     * @throws MissingParameterException if a required parameter is missing
     * @throws NoNodeAvailableException  if all the hosts are offline
     * @throws ClientResponseException   if the status code of response is 4xx
     * @throws ServerResponseException   if the status code of response is 5xx
     * @throws ClientException
     */
    public function restoreSnapshot($repository, $name, $options = [], $waitForCompletion = false): Response
    {
        $params = [
            'repository' => $repository,
            'snapshot' => $name,
            'body' => $options,
            'wait_for_completion' => (bool) $waitForCompletion,
        ];

        return $this->_client->toElasticaResponse(
            $this->_client->snapshot()->restore($params)
        );
    }
}
