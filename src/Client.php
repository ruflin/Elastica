<?php

namespace Elastica;

use Elastica\Bulk\Action;
use Elastica\Bulk\ResponseSet;
use Elastica\Exception\ConnectionException;
use Elastica\Exception\InvalidException;
use Elastica\Script\AbstractScript;
use Elasticsearch\Endpoints\AbstractEndpoint;
use Elasticsearch\Endpoints\Indices\ForceMerge;
use Elasticsearch\Endpoints\Indices\Refresh;
use Elasticsearch\Endpoints\Update;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Client to connect the the elasticsearch server.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class Client
{
    /**
     * @var ClientConfiguration
     */
    protected $_config;

    /**
     * @var callback
     */
    protected $_callback;

    /**
     * @var Connection\ConnectionPool
     */
    protected $_connectionPool;

    /**
     * @var Request|null
     */
    protected $_lastRequest;

    /**
     * @var Response|null
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
     * Creates a new Elastica client.
     *
     * @param array|string  $config   OPTIONAL Additional config or DSN of options
     * @param callback|null $callback OPTIONAL Callback function which can be used to be notified about errors (for example connection down)
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
     * Get current version.
     */
    public function getVersion(): string
    {
        if ($this->_version) {
            return $this->_version;
        }

        $data = $this->request('/')->getData();

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
     * @return array|string Config value
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
     *
     * @param string $name Index name to create connection to
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
     * @param array|\Elastica\Document[] $docs Array of Elastica\Document
     *
     * @throws InvalidException If docs is empty
     */
    public function updateDocuments(array $docs, array $requestParams = []): ResponseSet
    {
        if (empty($docs)) {
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
     * @param array|\Elastica\Document[] $docs Array of Elastica\Document
     *
     * @throws InvalidException If docs is empty
     */
    public function addDocuments(array $docs, array $requestParams = []): ResponseSet
    {
        if (empty($docs)) {
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
     * @param int|string                                               $id      document id
     * @param array|\Elastica\Document|\Elastica\Script\AbstractScript $data    raw data for request body
     * @param string                                                   $index   index to update
     * @param array                                                    $options array of query params to use for query. For possible options check es api
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/docs-update.html
     */
    public function updateDocument($id, $data, $index, array $options = []): Response
    {
        $endpoint = new Update();
        $endpoint->setID($id);
        $endpoint->setIndex($index);

        if ($data instanceof AbstractScript) {
            $requestData = $data->toArray();
        } elseif ($data instanceof Document) {
            $requestData = ['doc' => $data->getData()];

            if ($data->getDocAsUpsert()) {
                $requestData['doc_as_upsert'] = true;
            }

            $docOptions = $data->getOptions(
                [
                    'version',
                    'version_type',
                    'routing',
                    'percolate',
                    'parent',
                    'retry_on_conflict',
                    'consistency',
                    'replication',
                    'refresh',
                    'timeout',
                ]
            );
            $options += $docOptions;
        } else {
            $requestData = $data;
        }

        //If an upsert document exists
        if ($data instanceof AbstractScript || $data instanceof Document) {
            if ($data->hasUpsert()) {
                $requestData['upsert'] = $data->getUpsert()->getData();
            }
        }

        $endpoint->setBody($requestData);
        $endpoint->setParams($options);

        $response = $this->requestEndpoint($endpoint);

        if ($response->isOk()
            && $data instanceof Document
            && ($data->isAutoPopulate() || $this->getConfigValue(['document', 'autoPopulate'], false))
        ) {
            $responseData = $response->getData();
            if (isset($responseData['_version'])) {
                $data->setVersion($responseData['_version']);
            }
        }

        return $response;
    }

    /**
     * Bulk deletes documents.
     *
     * @param array|\Elastica\Document[] $docs
     *
     * @throws InvalidException
     */
    public function deleteDocuments(array $docs, array $requestParams = []): ResponseSet
    {
        if (empty($docs)) {
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
     * @return \Elastica\Status Status object
     */
    public function getStatus()
    {
        return new Status($this);
    }

    /**
     * Returns the current cluster.
     *
     * @return \Elastica\Cluster Cluster object
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
        return $this->_initConnections();
    }

    /**
     * @param \Elastica\Connection $connection
     *
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
     * @throws \Elastica\Exception\ClientException
     *
     * @return \Elastica\Connection
     */
    public function getConnection()
    {
        return $this->_connectionPool->getConnection();
    }

    /**
     * @return \Elastica\Connection[]
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
     * @param array|\Elastica\Connection[] $connections
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
     * @param array                  $ids     Document ids
     * @param \Elastica\Index|string $index   Index name
     * @param bool|string            $routing Optional routing key for all ids
     *
     * @throws InvalidException
     */
    public function deleteIds(array $ids, $index, $routing = false): ResponseSet
    {
        if (empty($ids)) {
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
     * @throws \Elastica\Exception\ResponseException
     * @throws InvalidException
     */
    public function bulk(array $params): ResponseSet
    {
        if (empty($params)) {
            throw new InvalidException('Array has to consist of at least one param');
        }

        $bulk = new Bulk($this);

        $bulk->addRawData($params);

        return $bulk->send();
    }

    /**
     * Makes calls to the elasticsearch server based on this index.
     *
     * It's possible to make any REST query directly over this method
     *
     * @param string       $path        Path to call
     * @param string       $method      Rest method to use (GET, POST, DELETE, PUT)
     * @param array|string $data        OPTIONAL Arguments as array or pre-encoded string
     * @param array        $query       OPTIONAL Query params
     * @param string       $contentType Content-Type sent with this request
     *
     * @throws Exception\ClientException|Exception\ConnectionException
     */
    public function request(string $path, string $method = Request::GET, $data = [], array $query = [], string $contentType = Request::DEFAULT_CONTENT_TYPE): Response
    {
        $connection = $this->getConnection();
        $request = $this->_lastRequest = new Request($path, $method, $data, $query, $connection, $contentType);
        $this->_lastResponse = null;

        try {
            $response = $this->_lastResponse = $request->send();
        } catch (ConnectionException $e) {
            $this->_connectionPool->onFail($connection, $e, $this);
            $this->_logger->error('Elastica Request Failure', [
                'exception' => $e,
                'request' => $e->getRequest()->toArray(),
                'retry' => $this->hasConnection(),
            ]);

            // In case there is no valid connection left, throw exception which caused the disabling of the connection.
            if (!$this->hasConnection()) {
                throw $e;
            }

            return $this->request($path, $method, $data, $query);
        }

        $this->_logger->debug('Elastica Request', [
            'request' => $request->toArray(),
            'response' => $this->_lastResponse ? $this->_lastResponse->getData() : null,
            'responseStatus' => $this->_lastResponse ? $this->_lastResponse->getStatus() : null,
        ]);

        return $response;
    }

    /**
     * Makes calls to the elasticsearch server with usage official client Endpoint.
     */
    public function requestEndpoint(AbstractEndpoint $endpoint): Response
    {
        return $this->request(
            \ltrim($endpoint->getURI(), '/'),
            $endpoint->getMethod(),
            null === $endpoint->getBody() ? [] : $endpoint->getBody(),
            $endpoint->getParams()
        );
    }

    /**
     * Force merges all search indices.
     *
     * @param array $args OPTIONAL Optional arguments
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-forcemerge.html
     */
    public function forcemergeAll($args = []): Response
    {
        $endpoint = new ForceMerge();
        $endpoint->setParams($args);

        return $this->requestEndpoint($endpoint);
    }

    /**
     * Refreshes all search indices.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-refresh.html
     */
    public function refreshAll(): Response
    {
        return $this->requestEndpoint(new Refresh());
    }

    public function getLastRequest(): ?Request
    {
        return $this->_lastRequest;
    }

    public function getLastResponse(): ?Response
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
        if (empty($connections)) {
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

        return $params;
    }
}
