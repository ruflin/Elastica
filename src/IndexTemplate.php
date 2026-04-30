<?php

declare(strict_types=1);

namespace Elastica;

use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\MissingParameterException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Elastic\Transport\Exception\NoNodeAvailableException;
use Elastica\Exception\ClientException;
use Elastica\Exception\InvalidException;

/**
 * Elastica index template object.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-templates.html
 */
class IndexTemplate
{
    /**
     * Index template name.
     *
     * @var string Index template name
     */
    protected string $_name;

    protected Client $_client;

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
     */
    public function delete(): Response
    {
        return $this->_client->toElasticaResponse(
            $this->_client->indices()->deleteIndexTemplate(['name' => $this->getName()])
        );
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
     */
    public function create(array $args = []): Response
    {
        return $this->_client->toElasticaResponse(
            $this->_client->indices()->putIndexTemplate(['name' => $this->getName(), 'body' => $args])
        );
    }

    /**
     * Checks if the given index template is already created.
     *
     * @throws MissingParameterException if a required parameter is missing
     * @throws NoNodeAvailableException  if all the hosts are offline
     * @throws ClientResponseException   if the status code of response is 4xx
     * @throws ServerResponseException   if the status code of response is 5xx
     * @throws ClientException
     */
    public function exists(): bool
    {
        $response = $this->_client->indices()->existsIndexTemplate(['name' => $this->getName()]);

        return 200 === $response->getStatusCode();
    }

    /**
     * Returns the index template name.
     */
    public function getName(): string
    {
        return $this->_name;
    }

    /**
     * Returns index template client.
     */
    public function getClient(): Client
    {
        return $this->_client;
    }
}
