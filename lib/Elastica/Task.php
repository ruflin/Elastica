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
     * @return Response
     */
    public function get($id, $waitForCompletion = false)
    {
        $getEndpoint = $this->retrieveEndpointGet();
        $getEndpoint->setTaskId($id);
        $getEndpoint->setParams(['wait_for_completion' => $waitForCompletion]);

        $response = $this->request($getEndpoint);

        if(empty($response)) {
            return $null;
        }

        return $response;
    }

    /**
     * @param string $id
     * @return mixed
     */
    public function isCompleted($id)
    {
        $task = $this->fetchById($id);
        return $task->getData()['completed'];
    }

    /**
     * @param string $type
     * @return Response
     */
    public function getByType($type)
    {
        $getEndpoint = $this->retriveEndpointGet();
        $getEndpoint->setType($type);
        return $this->request($getEndpoint);
    }

    /**
     * @param string $id
     * @return Response
     */
    public function cancel($id)
    {
        $cancelEndpoint = $this->retrieveEndpointCancel();
        $cancelEndpoint->setTaskId($id);
        return $this->request($cancelEndpoint);
    }

    public function getTasks()
    {
        $this->_params;

    }

    private function request(AbstractEndpoint $endpoint)
    {
        try {
            return $this->_client->requestEndpoint($endpoint);
        } catch (ResponseException $exception) {
            if($exception->getResponse()->getStatus() === 404) {
                return $exception->getResponse();
            }
            throw $e;
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
