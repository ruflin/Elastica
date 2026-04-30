<?php

declare(strict_types=1);

namespace Elastica;

use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\MissingParameterException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Elastic\Transport\Exception\NoNodeAvailableException;
use Elastica\Exception\ClientException;

/**
 * Represents elasticsearch task.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/tasks.html
 */
class Task extends Param
{
    public const WAIT_FOR_COMPLETION = 'wait_for_completion';
    public const WAIT_FOR_COMPLETION_FALSE = 'false';
    public const WAIT_FOR_COMPLETION_TRUE = 'true';

    /**
     * Task id, e.g. in form of nodeNumber:taskId.
     */
    protected string $_id;

    /**
     * @var Response
     */
    protected $_response;

    /**
     * @var array<string, mixed>
     */
    protected $_data;

    protected Client $_client;

    public function __construct(Client $client, string $id)
    {
        $this->_client = $client;
        $this->_id = $id;
    }

    /**
     * Returns task id.
     *
     * @return string
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Returns task data.
     *
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        if (null === $this->_data) {
            $this->refresh();
        }

        return $this->_data;
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
     * Refresh task status.
     *
     * @param array<string, mixed> $options
     *
     * @throws MissingParameterException if a required parameter is missing
     * @throws NoNodeAvailableException  if all the hosts are offline
     * @throws ClientResponseException   if the status code of response is 4xx
     * @throws ServerResponseException   if the status code of response is 5xx
     * @throws ClientException
     */
    public function refresh(array $options = []): void
    {
        $this->_response = $this->_client->toElasticaResponse(
            $this->_client->tasks()->get(\array_merge(['task_id' => $this->_id], $options))
        );
        $this->_data = $this->getResponse()->getData();
    }

    public function isCompleted(): bool
    {
        $data = $this->getData();

        return true === $data['completed'];
    }

    /**
     * @throws \Exception
     */
    public function cancel(): Response
    {
        if ('' === $this->_id) {
            throw new \Exception('No task id given');
        }

        return $this->_client->toElasticaResponse(
            $this->_client->tasks()->cancel(['task_id' => $this->_id])
        );
    }
}
