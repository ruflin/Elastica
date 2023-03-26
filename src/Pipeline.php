<?php

namespace Elastica;

use Elastica\Exception\ClientException;
use Elastica\Exception\ConnectionException;
use Elastica\Exception\InvalidException;
use Elastica\Exception\ResponseException;
use Elastica\Processor\AbstractProcessor;
use Elasticsearch\Endpoints\AbstractEndpoint;
use Elasticsearch\Endpoints\Ingest\DeletePipeline;
use Elasticsearch\Endpoints\Ingest\GetPipeline;
use Elasticsearch\Endpoints\Ingest\Pipeline\Delete;
use Elasticsearch\Endpoints\Ingest\Pipeline\Get;
use Elasticsearch\Endpoints\Ingest\Pipeline\Put;
use Elasticsearch\Endpoints\Ingest\PutPipeline;

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
    protected $_client;

    /**
     * @var AbstractProcessor[]
     * @phpstan-var array{processors?: AbstractProcessor[]}
     */
    protected $_processors = [];

    public function __construct(Client $client)
    {
        $this->_client = $client;
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

        if (empty($this->_processors['processors'])) {
            throw new InvalidException('You should set a valid processor of type Elastica\Processor\AbstractProcessor.');
        }

        // TODO: Use only PutPipeline when dropping support for elasticsearch/elasticsearch 7.x
        $endpoint = \class_exists(PutPipeline::class) ? new PutPipeline() : new Put();
        $endpoint->setId($this->id);
        $endpoint->setBody($this->toArray());

        return $this->requestEndpoint($endpoint);
    }

    /**
     * Get a Pipeline Object.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/get-pipeline-api.html
     */
    public function getPipeline(string $id): Response
    {
        // TODO: Use only GetPipeline when dropping support for elasticsearch/elasticsearch 7.x
        $endpoint = \class_exists(GetPipeline::class) ? new GetPipeline() : new Get();
        $endpoint->setId($id);

        return $this->requestEndpoint($endpoint);
    }

    /**
     * Delete a Pipeline.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/delete-pipeline-api.html
     */
    public function deletePipeline(string $id): Response
    {
        // TODO: Use only DeletePipeline when dropping support for elasticsearch/elasticsearch 7.x
        $endpoint = \class_exists(DeletePipeline::class) ? new DeletePipeline() : new Delete();
        $endpoint->setId($id);

        return $this->requestEndpoint($endpoint);
    }

    /**
     * Sets query as raw array. Will overwrite all already set arguments.
     *
     * @param array $processors array
     */
    public function setRawProcessors(array $processors): self
    {
        $this->_processors = $processors;

        return $this;
    }

    public function addProcessor(AbstractProcessor $processor): self
    {
        if (!$this->_processors) {
            $this->_processors['processors'] = $processor->toArray();
            $this->_params['processors'] = [];
        } else {
            $this->_processors['processors'] = \array_merge($this->_processors['processors'], $processor->toArray());
        }

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
        $this->setParam('processors', [$processors]);

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
        $this->_params['processors'] = [$this->_processors['processors']];

        return $this->getParams();
    }

    public function getClient(): Client
    {
        return $this->_client;
    }

    /**
     * Makes calls to the elasticsearch server with usage official client Endpoint based on this index.
     *
     * @throws ClientException
     * @throws ConnectionException
     * @throws ResponseException
     */
    public function requestEndpoint(AbstractEndpoint $endpoint): Response
    {
        $cloned = clone $endpoint;

        return $this->getClient()->requestEndpoint($cloned);
    }
}
