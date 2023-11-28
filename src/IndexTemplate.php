<?php

namespace Elastica;

use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\MissingParameterException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Elastic\Elasticsearch\Response\Elasticsearch;
use Elastic\Transport\Exception\NoNodeAvailableException;
use Elastica\Exception\ClientException;
use Elastica\Exception\InvalidException;

/**
 * Elastica index template object.
 *
 * @author Dmitry Balabka <dmitry.balabka@gmail.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-templates.html
 */
class IndexTemplate
{
    /**
     * Index template name.
     *
     * @var string Index pattern
     */
    protected $_name;

    /**
     * @var Client
     */
    protected $_client;

    /**
     * Creates a new index template object.
     *
     * @param string $name Index template name
     *
     * @throws InvalidException
     */
    public function __construct(Client $client, $name)
    {
        $this->_client = $client;

        if (!\is_scalar($name)) {
            throw new InvalidException('Index template should be a scalar type');
        }
        $this->_name = (string) $name;
    }

    /**
     * Deletes the index template.
     *
     * @throws MissingParameterException if a required parameter is missing
     * @throws NoNodeAvailableException  if all the hosts are offline
     * @throws ClientResponseException   if the status code of response is 4xx
     * @throws ServerResponseException   if the status code of response is 5xx
     * @throws ClientException
     *
     * @return Elasticsearch
     */
    public function delete()
    {
        return $this->_client->indices()->deleteTemplate(['name' => $this->getName()]);
    }

    /**
     * Creates a new index template with the given arguments.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-templates.html
     *
     * @param array $args OPTIONAL Arguments to use
     *
     * @throws MissingParameterException if a required parameter is missing
     * @throws NoNodeAvailableException  if all the hosts are offline
     * @throws ClientResponseException   if the status code of response is 4xx
     * @throws ServerResponseException   if the status code of response is 5xx
     * @throws ClientException
     *
     * @return Elasticsearch
     */
    public function create(array $args = [])
    {
        return $this->_client->indices()->putTemplate(['name' => $this->getName(), 'body' => $args]);
    }

    /**
     * Checks if the given index template is already created.
     *
     * @throws MissingParameterException if a required parameter is missing
     * @throws NoNodeAvailableException  if all the hosts are offline
     * @throws ClientResponseException   if the status code of response is 4xx
     * @throws ServerResponseException   if the status code of response is 5xx
     * @throws ClientException
     *
     * @return bool
     */
    public function exists()
    {
        $response = $this->_client->indices()->existsTemplate(['name' => $this->getName()]);

        return 200 === $response->getStatusCode();
    }

    /**
     * Returns the index template name.
     *
     * @return string Index name
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Returns index template client.
     *
     * @return Client
     */
    public function getClient()
    {
        return $this->_client;
    }
}
