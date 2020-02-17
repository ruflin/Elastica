<?php

namespace Elastica;

use Elastica\Exception\NotFoundException;
use Elastica\Exception\ResponseException;
use Elasticsearch\Endpoints\Snapshot\Restore;

/**
 * Class Snapshot.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/modules-snapshots.html
 */
class Snapshot
{
    /**
     * @var Client
     */
    protected $_client;

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
     * @return Response
     */
    public function registerRepository($name, $type, $settings = [])
    {
        $data = [
            'type' => $type,
            'settings' => $settings,
        ];

        return $this->request($name, Request::PUT, $data);
    }

    /**
     * Retrieve a repository record by name.
     *
     * @param string $name the name of the desired repository
     *
     * @throws Exception\ResponseException
     * @throws Exception\NotFoundException
     *
     * @return array
     */
    public function getRepository($name)
    {
        try {
            $response = $this->request($name);
        } catch (ResponseException $e) {
            if (404 == $e->getResponse()->getStatus()) {
                throw new NotFoundException("Repository '".$name."' does not exist.");
            }
            throw $e;
        }
        $data = $response->getData();

        return $data[$name];
    }

    /**
     * Retrieve all repository records.
     *
     * @return array
     */
    public function getAllRepositories()
    {
        return $this->request('_all')->getData();
    }

    /**
     * Create a new snapshot.
     *
     * @param string $repository        the name of the repository in which this snapshot should be stored
     * @param string $name              the name of this snapshot
     * @param array  $options           optional settings for this snapshot
     * @param bool   $waitForCompletion if true, the request will not return until the snapshot operation is complete
     *
     * @return Response
     */
    public function createSnapshot($repository, $name, $options = [], $waitForCompletion = false)
    {
        return $this->request($repository.'/'.$name, Request::PUT, $options, ['wait_for_completion' => $waitForCompletion]);
    }

    /**
     * Retrieve data regarding a specific snapshot.
     *
     * @param string $repository the name of the repository from which to retrieve the snapshot
     * @param string $name       the name of the desired snapshot
     *
     * @throws Exception\ResponseException
     * @throws Exception\NotFoundException
     *
     * @return array
     */
    public function getSnapshot($repository, $name)
    {
        try {
            $response = $this->request($repository.'/'.$name);
        } catch (ResponseException $e) {
            if (404 == $e->getResponse()->getStatus()) {
                throw new NotFoundException("Snapshot '".$name."' does not exist in repository '".$repository."'.");
            }
            throw $e;
        }
        $data = $response->getData();

        return $data['snapshots'][0];
    }

    /**
     * Retrieve data regarding all snapshots in the given repository.
     *
     * @param string $repository the repository name
     *
     * @return array
     */
    public function getAllSnapshots($repository)
    {
        return $this->request($repository.'/_all')->getData();
    }

    /**
     * Delete a snapshot.
     *
     * @param string $repository the repository in which the snapshot resides
     * @param string $name       the name of the snapshot to be deleted
     *
     * @return Response
     */
    public function deleteSnapshot($repository, $name)
    {
        return $this->request($repository.'/'.$name, Request::DELETE);
    }

    /**
     * Restore a snapshot.
     *
     * @param string $repository        the name of the repository
     * @param string $name              the name of the snapshot
     * @param array  $options           options for the restore operation
     * @param bool   $waitForCompletion if true, the request will not return until the restore operation is complete
     *
     * @return Response
     */
    public function restoreSnapshot($repository, $name, $options = [], $waitForCompletion = false)
    {
        $endpoint = new Restore();
        $endpoint->setRepository($repository);
        $endpoint->setSnapshot($name);
        $endpoint->setBody($options);
        $endpoint->setParams(['wait_for_completion' => $waitForCompletion]);

        return $this->_client->requestEndpoint($endpoint);
    }

    /**
     * Perform a snapshot request.
     *
     * @param string $path   the URL
     * @param string $method the HTTP method
     * @param array  $data   request body data
     * @param array  $query  query string parameters
     *
     * @return Response
     */
    public function request($path, $method = Request::GET, $data = [], array $query = [])
    {
        return $this->_client->request('_snapshot/'.$path, $method, $data, $query);
    }
}
