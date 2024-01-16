<?php

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
use GuzzleHttp\Psr7\Uri;
use Http\Promise\Promise;
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

    /**
     * @var ClientConfiguration
     */
    protected $_config;

    /**
     * @var callable
     */
    protected $_callback;

    /**
     * @var Connection\ConnectionPool
     */
    protected $_connectionPool;

    /**
     * @var RequestInterface|null
     */
    protected $_lastRequest;

    /**
     * @var Elasticsearch|null
     */
    protected $_lastResponse;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @var string
     */
    protected $_version;

    /**
     * The endpoint namespace storage.
     */
    protected array $namespace;

    /**
     * Creates a new Elastica client.
     *
     * @param array|string  $config   OPTIONAL Additional config or DSN of options
     * @param callable|null $callback OPTIONAL Callback function which can be used to be notified about errors (for example connection down)
     *
     * @throws InvalidException
     */
    public function __construct($config = [], ?callable $callback = null, ?LoggerInterface $logger = null)
    {
        if (\is_string($config)) {
            $configuration = ClientConfiguration::fromDsn($config);
        } elseif (\is_array($config)) {
            $configuration = ClientConfiguration::fromArray($config);
        } else {
            throw new InvalidException('Config parameter must be an array or a string.');
        }

        $this->_config = $configuration;
        $this->_callback = $callback;
        $this->_logger = $logger ?? new NullLogger();

        $this->_initConnections();
    }

    /**
     * {@inheritdoc}
     */
    public function getLogger(): LoggerInterface
    {
        return $this->_logger;
    }

    /**
     * {@inheritdoc}
     */
    public function getTransport(): Transport
    {
        throw new \Exception('Not supported');
    }

    /**
     * {@inheritdoc}
     */
    public function setAsync(bool $async): self
    {
        throw new \Exception('Not supported');
    }

    /**
     * {@inheritdoc}
     */
    public function getAsync(): bool
    {
        throw new \Exception('Not supported');
    }

    /**
     * {@inheritdoc}
     */
    public function setElasticMetaHeader(bool $active): self
    {
        $this->elasticMetaHeader = $active;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getElasticMetaHeader(): bool
    {
        return $this->elasticMetaHeader;
    }

    /**
     * {@inheritdoc}
     */
    public function setResponseException(bool $active): self
    {
        throw new \Exception('Not supported');
    }

    /**
     * {@inheritdoc}
     */
    public function getResponseException(): bool
    {
        throw new \Exception('Not supported');
    }

    /**
     * Get current version.
     *
     * @throws MissingParameterException if a required parameter is missing
     * @throws NoNodeAvailableException  if all the hosts are offline
     * @throws ClientResponseException   if the status code of response is 4xx
     * @throws ServerResponseException   if the status code of response is 5xx
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
     *
     * @return mixed
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
     * Adds a HTTP Header.
     */
    public function addHeader(string $header, string $value): self
    {
        if ($this->_config->has('headers')) {
            $headers = $this->_config->get('headers');
        } else {
            $headers = [];
        }
        $headers[$header] = $value;
        $this->_config->set('headers', $headers);

        return $this;
    }

    /**
     * Remove a HTTP Header.
     */
    public function removeHeader(string $header): self
    {
        if ($this->_config->has('headers')) {
            $headers = $this->_config->get('headers');
            unset($headers[$header]);
            $this->_config->set('headers', $headers);
        }

        return $this;
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
     *
     * @return Status
     */
    public function getStatus()
    {
        return new Status($this);
    }

    /**
     * Returns the current cluster.
     *
     * @return Cluster
     */
    public function getCluster()
    {
        return new Cluster($this);
    }

    /**
     * Establishes the client connections.
     */
    public function connect()
    {
        $this->_initConnections();
    }

    /**
     * @return $this
     */
    public function addConnection(Connection $connection)
    {
        $this->_connectionPool->addConnection($connection);

        return $this;
    }

    /**
     * Determines whether a valid connection is available for use.
     *
     * @return bool
     */
    public function hasConnection()
    {
        return $this->_connectionPool->hasConnection();
    }

    /**
     * @throws ClientException
     *
     * @return Connection
     */
    public function getConnection()
    {
        return $this->_connectionPool->getConnection();
    }

    /**
     * @return Connection[]
     */
    public function getConnections()
    {
        return $this->_connectionPool->getConnections();
    }

    /**
     * @return \Elastica\Connection\Strategy\StrategyInterface
     */
    public function getConnectionStrategy()
    {
        return $this->_connectionPool->getStrategy();
    }

    /**
     * @param array|Connection[] $connections
     *
     * @return $this
     */
    public function setConnections(array $connections)
    {
        $this->_connectionPool->setConnections($connections);

        return $this;
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

    public function baseBulk(array $params)
    {
        return $this->toElasticaResponse($this->elasticClientBulk($params));
    }

    public function sendRequest(RequestInterface $sentRequest): Elasticsearch|Promise
    {
        $connection = $this->getConnection();
        $transport = $connection->getTransportObject();

        $this->_lastRequest = $sentRequest;
        $this->_lastResponse = null;

        try {
            $response = $transport->sendRequest($sentRequest);

            $result = new Elasticsearch();
            $result->setResponse($response, 'HEAD' === $sentRequest->getMethod() ? false : true);

            $this->_lastResponse = $result;
        } catch (ServerResponseException|NoNodeAvailableException $e) {
            $this->_connectionPool->onFail($connection, $e, $this);
            $this->_logger->error('Elastica Request Failure', [
                'exception' => $e,
                'request' => $sentRequest,
                'request_content' => \json_decode($sentRequest->getBody()->__toString(), true),
                'retry' => $this->hasConnection(),
            ]);

            // In case there is no valid connection left, throw exception which caused the disabling of the connection.
            if (!$this->hasConnection()) {
                throw $e;
            }

            return $this->sendRequest($sentRequest);
        }

        $this->_logger->debug('Elastica Request', [
            'request' => \json_decode($sentRequest->getBody()->__toString(), true),
            'response' => 'HEAD' !== $sentRequest->getMethod() ? $result->asArray() : $result->asString(),
            'responseStatus' => $response->getStatusCode(),
        ]);

        return $result;
    }

    /**
     * Force merges all search indices.
     *
     * @param array $args OPTIONAL Optional arguments
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-forcemerge.html
     *
     * @throws MissingParameterException if a required parameter is missing
     * @throws NoNodeAvailableException  if all the hosts are offline
     * @throws ClientResponseException   if the status code of response is 4xx
     * @throws ServerResponseException   if the status code of response is 5xx
     * @throws ClientException
     */
    public function forcemergeAll($args = []): Response
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

    /**
     * Replace the existing logger.
     *
     * @return $this
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->_logger = $logger;

        return $this;
    }

    public function toElasticaResponse(Elasticsearch|ResponseInterface $elasticsearchResponse): Response
    {
        return ResponseConverter::toElastica($elasticsearchResponse);
    }

    /**
     * Inits the client connections.
     */
    protected function _initConnections(): void
    {
        $connections = [];

        foreach ($this->getConfig('connections') as $connection) {
            $connections[] = Connection::create($this->_prepareConnectionParams($connection));
        }

        if ($this->_config->has('servers')) {
            $servers = $this->_config->get('servers');
            foreach ($servers as $server) {
                $connections[] = Connection::create($this->_prepareConnectionParams($server));
            }
        }

        // If no connections set, create default connection
        if (!$connections) {
            $connections[] = Connection::create($this->_prepareConnectionParams($this->getConfig()));
        }

        if (!$this->_config->has('connectionStrategy')) {
            if (true === $this->getConfig('roundRobin')) {
                $this->setConfigValue('connectionStrategy', 'RoundRobin');
            } else {
                $this->setConfigValue('connectionStrategy', 'Simple');
            }
        }

        $strategy = Connection\Strategy\StrategyFactory::create($this->getConfig('connectionStrategy'));

        $this->_connectionPool = new Connection\ConnectionPool($connections, $strategy, $this->_callback);
    }

    /**
     * Creates a Connection params array from a Client or server config array.
     */
    protected function _prepareConnectionParams(array $config): array
    {
        $params = [];
        $params['config'] = [];
        foreach ($config as $key => $value) {
            if (\in_array($key, ['bigintConversion', 'curl', 'headers', 'url'])) {
                $params['config'][$key] = $value;
            } else {
                $params[$key] = $value;
            }
        }

        $params['transport'] = $this->_buildTransport($config);

        return $params;
    }

    protected function _buildTransport(array $config): Transport
    {
        $transportConfig = $config['transport_config'] ?? [];
        $hosts = [];

        if (isset($config['url'])) {
            $hosts = [$config['url']];
        } else {
            if (isset($config['hosts'])) {
                $hosts = $config['hosts'];
            } else {
                $hosts = [(string) Uri::fromParts([
                    'scheme' => $config['schema'] ?? 'http',
                    'host' => $config['host'] ?? Connection::DEFAULT_HOST,
                    'port' => $config['port'] ?? Connection::DEFAULT_PORT,
                    'path' => isset($config['path']) ? \ltrim($config['path'], '/') : '',
                ]),
                ];
            }
        }

        // Transport builder
        $builder = TransportBuilder::create();

        $builder->setHosts($hosts);

        // Logger
        if (null !== $this->_logger) {
            $builder->setLogger($this->_logger);
        }

        // Http client
        if (isset($transportConfig['http_client'])) {
            $builder->setClient($config['http_client']);
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
            $builder->setNodePool($config['node_pool']);
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
        if (isset($config['api_key']) && !empty($config['api_key'])) {
            if (isset($config['username']) && !empty($config['username'])) {
                throw new InvalidException('You cannot use APIKey and Basic Authenication together');
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
        if (false !== \strpos(\get_class($transport->getClient()), 'Symfony\Component\HttpClient')) {
            return true;
        }
        try {
            if (false !== \strpos(\get_class($transport->getAsyncClient()), 'Symfony\Component\HttpClient')) {
                return true;
            }
        } catch (NoAsyncClientException $e) {
            return false;
        }

        return false;
    }

    protected function setTransportClientOptions(HttpClientInterface $client, array $config, array $clientOptions = []): HttpClientInterface
    {
        if (empty($config) && empty($clientOptions)) {
            return $client;
        }
        $class = \get_class($client);
        if (!isset(AdapterOptions::HTTP_ADAPTERS[$class])) {
            throw new HttpClientException(\sprintf('The HTTP client %s is not supported for custom options', $class));
        }
        $adapterClass = AdapterOptions::HTTP_ADAPTERS[$class];
        if (!\class_exists($adapterClass) || !\in_array(AdapterInterface::class, \class_implements($adapterClass))) {
            throw new HttpClientException(\sprintf('The class %s does not exists or does not implement %s', $adapterClass, AdapterInterface::class));
        }
        $adapter = new $adapterClass();

        return $adapter->setConfig($client, $config, $clientOptions);
    }
}
