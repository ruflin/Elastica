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
     * Indicates if we should use the legacy template API.
     *
     * @var bool
     */
    protected $_useLegacy;

    /**
     * Legacy template object if using the legacy API.
     *
     * @var Template
     */
    protected $_legacyTemplate;

    /**
     * Creates a new index template object.
     *
     * @param string $name Index template name
     *
     * @throws InvalidException
     */
    public function __construct(Client $client, $name, $useLegacy = true)
    {
        $this->_useLegacy = $useLegacy;
        if ($useLegacy) {
            $this->_legacyTemplate = new Template($client, $name);

            return;
        }

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
        if ($this->_useLegacy) {
            return $this->_legacyTemplate->delete();
        }

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
        if ($this->_useLegacy) {
            return $this->_legacyTemplate->create($args);
        }

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
        if ($this->_useLegacy) {
            return $this->_legacyTemplate->exists();
        }
        $response = $this->_client->indices()->existsIndexTemplate(['name' => $this->getName()]);

        return 200 === $response->getStatusCode();
    }

    /**
     * Returns the index template name.
     */
    public function getName(): string
    {
        if ($this->_useLegacy) {
            return $this->_legacyTemplate->getName();
        }

        return $this->_name;
    }

    /**
     * Returns index template client.
     */
    public function getClient(): Client
    {
        if ($this->_useLegacy) {
            return $this->_legacyTemplate->getClient();
        }

        return $this->_client;
    }
}
