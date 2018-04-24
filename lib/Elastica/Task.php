<?php

namespace Elastica;

use Elastica\Exception\ResponseException;
use Elastica\Query\AbstractQuery;
use Elasticsearch\Endpoints\AbstractEndpoint;
use Elasticsearch\Endpoints\Tasks\Cancel;
use Elasticsearch\Endpoints\Tasks\Get;
use Elasticsearch\Endpoints\Tasks\TasksList;

class Task extends Param
{
    /** @var Client */
    protected $_client;

    /** @var Get */
    protected $_endpointGet;

    /** @var Cancel */
    protected $_endpointCancel;

    /** @var TasksList */
    protected $_endpointTaskLists;

    public function __construct(Client $client)
    {
        $this->_client = $client;
    }

    /**
     * @param string $id
     * @param bool $waitForCompletion
     * @return array
     */
    public function get(string $id, bool $waitForCompletion = false): array
    {
        $getEndpoint = $this->retrieveEndpointGet();
        $getEndpoint->setTaskId($id);
        $getEndpoint->setParams(['wait_for_completion' => $waitForCompletion ? 'true' : 'false']);

        $response = $this->request($getEndpoint);

        if(empty($response)) {
            return [];
        }

        return $response->getData();
    }

    /**
     * @param string $id
     * @return mixed
     */
    public function isCompleted(string $id): bool
    {
        $task = $this->get($id);
        return $task['completed'];
    }

    /**
     * @param string $type
     * @return Response
     */
    public function getByType(string $type): array
    {
        $getEndpoint = $this->retriveEndpointGet();
        $getEndpoint->setType($type);
        return $this->request($getEndpoint)->getData();
    }

    /**
     * @param string $id
     * @return Response
     */
    public function cancel(string $id): Response
    {
        $cancelEndpoint = $this->retrieveEndpointCancel();
        $cancelEndpoint->setTaskId($id);
        return $this->request($cancelEndpoint);
    }

    public function getTasks(): array
    {
        $getEndpoint = $this->retrieveEndpointGet();
        return $this->request($getEndpoint)->getData();
    }

    /**
     * @param AbstractEndpoint $endpoint
     * @return Response
     */
    private function request(AbstractEndpoint $endpoint): Response
    {
        try {
            return $this->_client->requestEndpoint($endpoint);
        } catch (ResponseException $exception) {
            if($exception->getResponse()->getStatus() === 404) {
                return $exception->getResponse();
            }
            throw $exception;
        }
    }

    private function retrieveEndpointGet()
    {
        if (empty($this->_endpointGet)) {
            $this->_endpointGet = new Get();
        }
        return $this->_endpointGet;
    }

    private function retrieveEndpointCancel()
    {
        if (empty($this->_endpointCancel)) {
            $this->_endpointCancel = new Cancel();
        }
        return $this->_endpointCancel;
    }

    /**
     * @param Get $endpointGet
     * @return Task
     */
    public function setEndpointGet(Get $endpointGet)
    {
        $this->_endpointGet = $endpointGet;
        return $this;
    }

    /**
     * @param Cancel $endpointCancel
     * @return Task
     */
    public function setEndpointCancel(Cancel $endpointCancel)
    {
        $this->_endpointCancel = $endpointCancel;
        return $this;
    }

    /**
     * @param TasksList $endpointTaskLists
     * @return Task
     */
    public function setEndpointTaskLists(TasksList $endpointTaskLists)
    {
        $this->_endpointTaskLists = $endpointTaskLists;
        return $this;
    }

}
