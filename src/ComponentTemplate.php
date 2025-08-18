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
 * Elastica component template object.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-templates.html
 */
class ComponentTemplate
{
    /**
     * Component template name.
     *
     * @var string Component template name
     */
    protected $_name;

    /**
     * @var Client
     */
    protected $_client;

    /**
     * Creates a new component template object.
     *
     * @param string $name Component template name
     *
     * @throws InvalidException
     */
    public function __construct(Client $client, $name)
    {
        $this->_client = $client;

        if (!\is_scalar($name)) {
            throw new InvalidException('Component template should be a scalar type');
        }
        $this->_name = (string) $name;
    }

    /**
     * Deletes the component template.
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
            $this->_client->cluster()->deleteComponentTemplate(['name' => $this->getName()])
        );
    }

    /**
     * Creates a new component template with the given arguments.
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
            $this->_client->cluster()->putComponentTemplate(['name' => $this->getName(), 'body' => $args])
        );
    }

    /**
     * Checks if the given component template is already created.
     *
     * @throws MissingParameterException if a required parameter is missing
     * @throws NoNodeAvailableException  if all the hosts are offline
     * @throws ClientResponseException   if the status code of response is 4xx
     * @throws ServerResponseException   if the status code of response is 5xx
     * @throws ClientException
     */
    public function exists(): bool
    {
        $response = $this->_client->cluster()->existsComponentTemplate(['name' => $this->getName()]);

        return 200 === $response->getStatusCode();
    }

    /**
     * Returns the component template name.
     */
    public function getName(): string
    {
        return $this->_name;
    }

    /**
     * Returns component template client.
     */
    public function getClient(): Client
    {
        return $this->_client;
    }
}
