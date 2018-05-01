<?php

namespace Elastica;

use Elastica\Exception\ResponseException;
use Elastica\Query\AbstractQuery;
use Elasticsearch\Endpoints\AbstractEndpoint;

class Task extends Param
{
    const WAIT_FOR_COMPLETION = 'wait_for_completion';
    const WAIT_FOR_COMPLETION_FALSE = 'false';
    const WAIT_FOR_COMPLETION_TRUE = 'true';

    /**
     * Task id, e.g. in form of nodeNumber:taskId
     *
     * @var string
     */
    protected $_id;

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
     * Endpoint object for task getting.
     *
     * @var \Elasticsearch\Endpoints\Tasks\Get
     */
    protected $_endpointGet;

    /**
     * Endpoint object for task canceling.
     *
     * @var \Elasticsearch\Endpoints\Tasks\Cancel
     */
    protected $_endpointCancel;

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
     * @return array Task data
     */
    public function getData(): array
    {
        if (is_null($this->_data)) {
            $this->refresh();
        }

        return $this->_data;
    }

    /**
     * Returns response object
     *
     * @return \Elastica\Response
     */
    public function getResponse(): Response
    {
        if (is_null($this->_response)) {
            $this->refresh();
        }

        return $this->_response;
    }

    /**
     * Refresh task status
     *
     * @param array $options Options for endpoint
     */
    public function refresh(array $options = [])
    {
        $endpoint = $this->retrieveEndpointGet();
        $endpoint->setTaskId($this->_id);
        $endpoint->setParams($options);

        $this->_response = $this->_client->requestEndpoint($endpoint);
        $this->_data = $this->getResponse()->getData();
    }

    /**
     * @return bool
     */
    public function isCompleted(): bool
    {
        $data = $this->getData();

        return $data['completed'] === true;
    }

    /**
     * Returns the injected endpoint or creates a default one.
     *
     * @return \Elasticsearch\Endpoints\Tasks\Get Task getting endpoint object
     */
    private function retrieveEndpointGet()
    {
        if (empty($this->_endpointGet)) {
            // TODO clone?
            $this->_endpointGet = new \Elasticsearch\Endpoints\Tasks\Get();
        }
        return $this->_endpointGet;
    }

    /**
     * Returns the injected endpoint or creates a default one.
     *
     * @return Cancel Task cancelation endpoint object
     */
    private function retrieveEndpointCancel()
    {
        if (empty($this->_endpointCancel)) {
            $this->_endpointCancel = new \Elasticsearch\Endpoints\Tasks\Cancel();
        }
        return $this->_endpointCancel;
    }

    /**
     * @param \Elasticsearch\Endpoints\Tasks\Get $endpointGet
     * @return Task
     */
    public function setEndpointGet(\Elasticsearch\Endpoints\Tasks\Get $endpointGet)
    {
        $this->_endpointGet = $endpointGet;
        return $this;
    }

    /**
     * @param \Elasticsearch\Endpoints\Tasks\Cancel $endpointCancel
     * @return Task
     */
    public function setEndpointCancel(\Elasticsearch\Endpoints\Tasks\Cancel $endpointCancel)
    {
        $this->_endpointCancel = $endpointCancel;
        return $this;
    }
}
