<?php

declare(strict_types=1);

namespace Elastica;

use Elastica\Exception\InvalidException;
use Elastica\Processor\AbstractProcessor;

/**
 * Elastica Pipeline object.
 *
 * Handles Pipeline management & definition.
 *
 * @author   Federico Panini <fpanini@gmail.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/ingest-processors.html
 */
class Pipeline extends Param
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var Client Client object
     */
    protected Client $_client;

    public function __construct(Client $client)
    {
        $this->_client = $client;
        $this->setParam('processors', []);
    }

    /**
     * Create a Pipeline.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/put-pipeline-api.html
     */
    public function create(): Response
    {
        if (empty($this->id)) {
            throw new InvalidException('You should set a valid pipeline id');
        }

        if (empty($this->_params['description'])) {
            throw new InvalidException('You should set a valid processor description.');
        }

        if (empty($this->_params['processors'])) {
            throw new InvalidException('You should set a valid processor of type Elastica\Processor\AbstractProcessor.');
        }

        return $this->_client->toElasticaResponse(
            $this->_client->ingest()->putPipeline(['id' => $this->id, 'body' => $this->toArray()])
        );
    }

    /**
     * Get a Pipeline Object.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/get-pipeline-api.html
     */
    public function getPipeline(string $id): Response
    {
        return $this->_client->toElasticaResponse(
            $this->_client->ingest()->getPipeline(['id' => $id])
        );
    }

    /**
     * Delete a Pipeline.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/delete-pipeline-api.html
     */
    public function deletePipeline(string $id): Response
    {
        return $this->_client->toElasticaResponse(
            $this->_client->ingest()->deletePipeline(['id' => $id])
        );
    }

    /**
     * Sets query as raw array. Will overwrite all already set arguments.
     *
     * @param array $processors array
     */
    public function setRawProcessors(array $processors): self
    {
        $this->setParam('processors', $processors);

        return $this;
    }

    public function addProcessor(AbstractProcessor $processor): self
    {
        $this->setParam('processors', \array_merge($this->getParam('processors'), [$processor->toArray()]));

        return $this;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param AbstractProcessor[] $processors
     */
    public function setProcessors(array $processors): self
    {
        foreach ($processors as $processor) {
            $this->addProcessor($processor);
        }

        return $this;
    }

    public function setDescription(string $description): self
    {
        $this->setParam('description', $description);

        return $this;
    }

    /**
     * Converts the params to an array. A default implementation exist to create
     * the an array out of the class name (last part of the class name)
     * and the params.
     */
    public function toArray(): array
    {
        return $this->getParams();
    }

    public function getClient(): Client
    {
        return $this->_client;
    }
}
