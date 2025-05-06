<?php

declare(strict_types=1);

namespace Elastica;

use Elastic\Elasticsearch\ClientInterface;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\HttpClientException;
use Elastic\Elasticsearch\Exception\MissingParameterException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Elastic\Elasticsearch\Response\Elasticsearch;
use Elastic\Elasticsearch\Traits\ClientEndpointsTrait;
use Elastic\Elasticsearch\Traits\EndpointTrait;
use Elastic\Elasticsearch\Traits\NamespaceTrait;
use Elastic\Elasticsearch\Transport\Adapter\AdapterInterface;
use Elastic\Elasticsearch\Transport\Adapter\AdapterOptions;
use Elastic\Transport\Exception\NoAsyncClientException;
use Elastic\Transport\Exception\NoNodeAvailableException;
use Elastic\Transport\Transport;
use Elastic\Transport\TransportBuilder;
use Elastica\Bulk\Action;
use Elastica\Bulk\ResponseSet;
use Elastica\Exception\Bulk\ResponseException as BulkResponseException;
use Elastica\Exception\ClientException;
use Elastica\Exception\InvalidException;
use Elastica\Script\AbstractScript;
use Psr\Http\Client\ClientInterface as HttpClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Client to connect the elasticsearch server.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class Client implements ClientInterface
{
    use EndpointTrait;
    use NamespaceTrait;
    use ClientEndpointsTrait {
        closePointInTime as protected elasticClientClosePointInTime;
        bulk as protected elasticClientBulk;
    }

    private bool $elasticMetaHeader = true;

    protected ClientConfiguration $_config;

    protected ?RequestInterface $_lastRequest = null;

    protected ?Elasticsearch $_lastResponse = null;

    protected LoggerInterface $_logger;

    protected ?string $_version = null;

    private Transport $_transport;

    public function __construct(string|array $config = [], ?LoggerInterface $logger = null)
    {
        $config = \is_string($config) ? ['hosts' => [$config]] : $config;

        $this->_config = ClientConfiguration::fromArray($config);
        $this->_logger = $logger ?? new NullLogger();
        $this->_transport = $this->_buildTransport($this->getConfig());
    }

    public function getLogger(): LoggerInterface
    {
        return $this->_logger;
    }

    public function getTransport(): Transport
    {
        return $this->_transport;
    }

    public function setAsync(bool $async): self
    {
        throw new \Exception('Not supported');
    }

    public function getAsync(): bool
    {
        throw new \Exception('Not supported');
    }

    public function setElasticMetaHeader(bool $active): self
    {
        $this->elasticMetaHeader = $active;

        return $this;
    }

    public function getElasticMetaHeader(): bool
    {
        return $this->elasticMetaHeader;
    }

    public function setResponseException(bool $active): self
    {
        throw new \Exception('Not supported');
    }

    public function getResponseException(): bool
    {
        throw new \Exception('Not supported');
    }

    public function setServerless(bool $value): ClientInterface
    {
        throw new \Exception('Not supported');
    }

    public function getServerless(): bool
    {
        return false;
    }

    /**
     * Get current version.
     *
     * @throws NoNodeAvailableException if all the hosts are offline
     * @throws ClientResponseException  if the status code of response is 4xx
     * @throws ServerResponseException  if the status code of response is 5xx
     * @throws ClientException
     */
    public function getVersion(): string
    {
        if ($this->_version) {
            return $this->_version;
        }

        $data = $this->info()->asArray();

        return $this->_version = $data['version']['number'];
    }

    /**
     * Sets specific config values (updates and keeps default values).
     *
     * @param array $config Params
     */
    public function setConfig(array $config): self
    {
        foreach ($config as $key => $value) {
            $this->_config->set($key, $value);
        }

        return $this;
    }

    /**
     * Returns a specific config key or the whole config array if not set.
     *
     * @throws InvalidException if the given key is not found in the configuration
     *
     * @return array|bool|string
     */
    public function getConfig(string $key = '')
    {
        return $this->_config->get($key);
    }

    /**
     * Sets / overwrites a specific config value.
     *
     * @param mixed $value Value
     */
    public function setConfigValue(string $key, $value): self
    {
        return $this->setConfig([$key => $value]);
    }

    /**
     * @param array|string $keys    config key or path of config keys
     * @param mixed        $default default value will be returned if key was not found
     */
    public function getConfigValue($keys, $default = null)
    {
        $value = $this->_config->getAll();
        foreach ((array) $keys as $key) {
            if (isset($value[$key])) {
                $value = $value[$key];
            } else {
                return $default;
            }
        }

        return $value;
    }

    /**
     * Returns the index for the given connection.
     */
    public function getIndex(string $name): Index
    {
        return new Index($this, $name);
    }

    /**
     * Uses _bulk to send documents to the server.
     *
     * Array of \Elastica\Document as input. Index has to be set inside the
     * document, because for bulk settings documents, documents can belong to
     * any index
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/docs-bulk.html
     *
     * @param array|Document[] $docs Array of Elastica\Document
     *
     * @throws InvalidException          If docs is empty
     * @throws MissingParameterException if a required parameter is missing
     * @throws NoNodeAvailableException  if all the hosts are offline
     * @throws ClientResponseException   if the status code of response is 4xx
     * @throws ServerResponseException   if the status code of response is 5xx
     * @throws BulkResponseException
     * @throws ClientException
     */
    public function updateDocuments(array $docs, array $requestParams = []): ResponseSet
    {
        if (!$docs) {
            throw new InvalidException('Array has to consist of at least one element');
        }

        $bulk = new Bulk($this);

        $bulk->addDocuments($docs, Action::OP_TYPE_UPDATE);
        foreach ($requestParams as $key => $value) {
            $bulk->setRequestParam($key, $value);
        }

        return $bulk->send();
    }

    /**
     * Uses _bulk to send documents to the server.
     *
     * Array of \Elastica\Document as input. Index has to be set inside the
     * document, because for bulk settings documents, documents can belong to
     * any index
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/docs-bulk.html
     *
     * @param array|Document[] $docs Array of Elastica\Document
     *
     * @throws InvalidException          If docs is empty
     * @throws MissingParameterException if a required parameter is missing
     * @throws NoNodeAvailableException  if all the hosts are offline
     * @throws ClientResponseException   if the status code of response is 4xx
     * @throws ServerResponseException   if the status code of response is 5xx
     * @throws BulkResponseException
     * @throws ClientException
     */
    public function addDocuments(array $docs, array $requestParams = []): ResponseSet
    {
        if (!$docs) {
            throw new InvalidException('Array has to consist of at least one element');
        }

        $bulk = new Bulk($this);

        $bulk->addDocuments($docs);

        foreach ($requestParams as $key => $value) {
            $bulk->setRequestParam($key, $value);
        }

        return $bulk->send();
    }

    /**
     * Update document, using update script. Requires elasticsearch >= 0.19.0.
     *
     * @param int|string                    $id      document id
     * @param AbstractScript|array|Document $data    raw data for request body
     * @param string                        $index   index to update
     * @param array                         $options array of query params to use for query. For possible options check es api
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/docs-update.html
     *
     * @throws MissingParameterException if a required parameter is missing
     * @throws NoNodeAvailableException  if all the hosts are offline
     * @throws ClientResponseException   if the status code of response is 4xx
     * @throws ServerResponseException   if the status code of response is 5xx
     * @throws ClientException
     */
    public function updateDocument($id, $data, $index, array $options = []): Response
    {
        $params = [
            'id' => $id,
            'index' => $index,
        ];

        if ($data instanceof AbstractScript) {
            $requestData = $data->toArray();
        } elseif ($data instanceof Document) {
            $requestData = ['doc' => $data->getData()];

            if ($data->getDocAsUpsert()) {
                $requestData['doc_as_upsert'] = true;
            }

            $docOptions = $data->getOptions(
                [
                    'consistency',
                    'parent',
                    'percolate',
                    'refresh',
                    'replication',
                    'retry_on_conflict',
                    'routing',
                    'timeout',
                ]
            );
            $options += $docOptions;
        } else {
            $requestData = $data;
        }

        // If an upsert document exists
        if ($data instanceof AbstractScript || $data instanceof Document) {
            if ($data->hasUpsert()) {
                $requestData['upsert'] = $data->getUpsert()->getData();
            }
        }

        $params['body'] = $requestData;

        $response = $this->update(\array_merge($params, $options));

        if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300
            && $data instanceof Document
            && ($data->isAutoPopulate() || $this->getConfigValue(['document', 'autoPopulate'], false))
        ) {
            $data->setVersionParams($response->asArray());
        }

        return $this->toElasticaResponse($response);
    }

    /**
     * Bulk deletes documents.
     *
     * @param array|Document[] $docs
     *
     * @throws InvalidException
     * @throws MissingParameterException if a required parameter is missing
     * @throws NoNodeAvailableException  if all the hosts are offline
     * @throws ClientResponseException   if the status code of response is 4xx
     * @throws ServerResponseException   if the status code of response is 5xx
     * @throws BulkResponseException
     * @throws ClientException
     */
    public function deleteDocuments(array $docs, array $requestParams = []): ResponseSet
    {
        if (!$docs) {
            throw new InvalidException('Array has to consist of at least one element');
        }

        $bulk = new Bulk($this);
        $bulk->addDocuments($docs, Action::OP_TYPE_DELETE);

        foreach ($requestParams as $key => $value) {
            $bulk->setRequestParam($key, $value);
        }

        return $bulk->send();
    }

    /**
     * Returns the status object for all indices.
     */
    public function getStatus(): Status
    {
        return new Status($this);
    }

    /**
     * Returns the current cluster.
     */
    public function getCluster(): Cluster
    {
        return new Cluster($this);
    }

    /**
     * Deletes documents with the given ids, index, type from the index.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/docs-bulk.html
     *
     * @param array        $ids     Document ids
     * @param Index|string $index   Index name
     * @param bool|string  $routing Optional routing key for all ids
     *
     * @throws InvalidException
     * @throws MissingParameterException if a required parameter is missing
     * @throws NoNodeAvailableException  if all the hosts are offline
     * @throws ClientResponseException   if the status code of response is 4xx
     * @throws ServerResponseException   if the status code of response is 5xx
     * @throws BulkResponseException
     * @throws ClientException
     */
    public function deleteIds(array $ids, $index, $routing = false): ResponseSet
    {
        if (!$ids) {
            throw new InvalidException('Array has to consist of at least one id');
        }

        $bulk = new Bulk($this);
        $bulk->setIndex($index);

        foreach ($ids as $id) {
            $action = new Action(Action::OP_TYPE_DELETE);
            $action->setId($id);

            if (!empty($routing)) {
                $action->setRouting($routing);
            }

            $bulk->addAction($action);
        }

        return $bulk->send();
    }

    /**
     * Bulk operation.
     *
     * Every entry in the params array has to exactly on array
     * of the bulk operation. An example param array would be:
     *
     * array(
     *         array('index' => array('_index' => 'test', '_id' => '1')),
     *         array('field1' => 'value1'),
     *         array('delete' => array('_index' => 'test', '_id' => '2')),
     *         array('create' => array('_index' => 'test', '_id' => '3')),
     *         array('field1' => 'value3'),
     *         array('update' => array('_id' => '1', '_index' => 'test')),
     *         array('doc' => array('field2' => 'value2')),
     * );
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/docs-bulk.html
     *
     * @throws InvalidException
     * @throws MissingParameterException if a required parameter is missing
     * @throws NoNodeAvailableException  if all the hosts are offline
     * @throws ClientResponseException   if the status code of response is 4xx
     * @throws ServerResponseException   if the status code of response is 5xx
     * @throws BulkResponseException
     * @throws ClientException
     */
    public function bulk(array $params): ResponseSet
    {
        if (!$params) {
            throw new InvalidException('Array has to consist of at least one param');
        }

        $bulk = new Bulk($this);

        $bulk->addRawData($params);

        return $bulk->send();
    }

    public function baseBulk(array $params): Response
    {
        return $this->toElasticaResponse($this->elasticClientBulk($params));
    }

    public function sendRequest(RequestInterface $request): Elasticsearch
    {
        $this->_lastRequest = $request;
        $this->_lastResponse = null;

        try {
            $response = $this->_transport->sendRequest($request);

            $result = new Elasticsearch();
            $result->setResponse($response, 'HEAD' !== $request->getMethod());

            $this->_lastResponse = $result;
        } catch (ServerResponseException|NoNodeAvailableException $e) {
            $this->_logger->error('Elastica Request Failure', [
                'exception' => $e,
                'request' => $request,
                'request_content' => \json_decode($request->getBody()->__toString(), true),
            ]);

            throw $e;
        }

        $this->_logger->debug('Elastica Request', [
            'request' => \json_decode($request->getBody()->__toString(), true),
            'response' => 'HEAD' !== $request->getMethod() ? $result->asArray() : $result->asString(),
            'responseStatus' => $response->getStatusCode(),
        ]);

        return $result;
    }

    /**
     * Force merges all search indices.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-forcemerge.html
     *
     * @throws NoNodeAvailableException if all the hosts are offline
     * @throws ClientResponseException  if the status code of response is 4xx
     * @throws ServerResponseException  if the status code of response is 5xx
     * @throws ClientException
     */
    public function forcemergeAll(array $args = []): Response
    {
        return $this->toElasticaResponse($this->indices()->forcemerge($args));
    }

    /**
     * Closes the given PointInTime.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/point-in-time-api.html#close-point-in-time-api
     *
     * @throws NoNodeAvailableException if all the hosts are offline
     * @throws ClientResponseException  if the status code of response is 4xx
     * @throws ServerResponseException  if the status code of response is 5xx
     * @throws ClientException
     */
    public function closePointInTime(string $pointInTimeId): Response
    {
        return $this->toElasticaResponse($this->elasticClientClosePointInTime(['body' => ['id' => $pointInTimeId]]));
    }

    /**
     * Refreshes all search indices.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-refresh.html
     *
     * @throws NoNodeAvailableException if all the hosts are offline
     * @throws ClientResponseException  if the status code of response is 4xx
     * @throws ServerResponseException  if the status code of response is 5xx
     * @throws ClientException
     */
    public function refreshAll(): Response
    {
        return $this->toElasticaResponse($this->indices()->refresh());
    }

    public function getLastRequest(): ?RequestInterface
    {
        return $this->_lastRequest;
    }

    public function getLastResponse(): ?Elasticsearch
    {
        return $this->_lastResponse;
    }

    public function toElasticaResponse(Elasticsearch|ResponseInterface $elasticsearchResponse): Response
    {
        return ResponseConverter::toElastica($elasticsearchResponse);
    }

    protected function _buildTransport(array $config): Transport
    {
        $hosts = isset($config['hosts']) && \is_array($config['hosts']) ? $config['hosts'] : [ClientConfiguration::DEFAULT_HOST];
        $transportConfig = $config['transport_config'] ?? [];

        $builder = TransportBuilder::create();

        $builder->setHosts($hosts);
        $builder->setLogger($this->_logger);

        // Http client
        if (isset($transportConfig['http_client'])) {
            $builder->setClient($transportConfig['http_client']);
        }

        // Set HTTP client options
        $builder->setClient(
            $this->setTransportClientOptions(
                $builder->getClient(),
                $transportConfig['http_client_config'] ?? [],
                $transportConfig['http_client_options'] ?? []
            )
        );

        // Cloud id
        if (isset($config['cloud_id'])) {
            $builder->setCloudId($config['cloud_id']);
        }

        // Node Pool
        if (isset($transportConfig['node_pool'])) {
            $builder->setNodePool($transportConfig['node_pool']);
        }

        $transport = $builder->build();

        // The default retries is equal to the number of hosts
        if (isset($config['retries']) && (int) $config['retries'] > 0) {
            $transport->setRetries($config['retries']);
        } else {
            $transport->setRetries(\count($hosts));
        }

        // Basic authentication
        if (isset($config['username'], $config['password'])) {
            $transport->setUserInfo($config['username'], $config['password']);
        }

        // API key
        if (!empty($config['api_key'])) {
            if (!empty($config['username'])) {
                throw new InvalidException('You cannot use APIKey and Basic Authentication together.');
            }

            $transport->setHeader('Authorization', \sprintf('ApiKey %s', $config['api_key']));
        }

        /*
         * Elastic cloud optimized with gzip
         * @see https://github.com/elastic/elasticsearch-php/issues/1241 omit for Symfony HTTP Client
         */
        if (isset($config['cloud_id']) && !$this->isSymfonyHttpClient($transport)) {
            $transport->setHeader('Accept-Encoding', 'gzip');
        }

        return $transport;
    }

    /**
     * Returns true if the transport HTTP client is Symfony.
     */
    protected function isSymfonyHttpClient(Transport $transport): bool
    {
        if (\str_contains($transport->getClient()::class, 'Symfony\Component\HttpClient')) {
            return true;
        }

        try {
            return \str_contains($transport->getAsyncClient()::class, 'Symfony\Component\HttpClient');
        } catch (NoAsyncClientException $e) {
            return false;
        }
    }

    protected function setTransportClientOptions(HttpClientInterface $client, array $config, array $clientOptions = []): HttpClientInterface
    {
        if (empty($config) && empty($clientOptions)) {
            return $client;
        }

        $adapterClass = AdapterOptions::HTTP_ADAPTERS[$client::class] ?? throw new HttpClientException(\sprintf('The HTTP client %s is not supported for custom options', $client::class));

        if (!\class_exists($adapterClass) || !\in_array(AdapterInterface::class, \class_implements($adapterClass), true)) {
            throw new HttpClientException(\sprintf('The class %s does not exists or does not implement %s', $adapterClass, AdapterInterface::class));
        }

        return (new $adapterClass())->setConfig($client, $config, $clientOptions);
    }
}
