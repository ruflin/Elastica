<?php

namespace Elastica;

use Elasticsearch\Endpoints\Tasks;

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
     *
     * @var string
     */
    protected $_id;

    /**
     * @var Response
     */
    protected $_response;

    /**
     * @var array
     */
    protected $_data;

    /**
     * @var Client
     */
    protected $_client;

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
     */
    public function refresh(array $options = []): void
    {
        $endpoint = (new Tasks\Get())
            ->setTaskId($this->_id)
            ->setParams($options)
        ;

        $this->_response = $this->_client->requestEndpoint($endpoint);
        $this->_data = $this->getResponse()->getData();
    }

    public function isCompleted(): bool
    {
        $data = $this->getData();

        return true === $data['completed'];
    }

    public function cancel(): Response
    {
        if ('' === $this->_id) {
            throw new \Exception('No task id given');
        }

        $endpoint = (new Tasks\Cancel())
            ->setTaskId($this->_id)
        ;

        return $this->_client->requestEndpoint($endpoint);
    }
}
