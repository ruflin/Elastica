<?php

namespace Elastica;

use Elastica\Bulk\Action;
use Elastica\Connection\ConnectionPoolInterface;
use Elastica\Connection\PoolBuilder;
use Elastica\Exception\ConnectionException;
use Elastica\Exception\InvalidException;
use Elastica\Script\AbstractScript;
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
     * @var bool
     */
    private $_autoPopulate = false;

    /**
     * @var Connection\ConnectionPool
     */
    protected $_connectionPool;

    /**
     * @var \Elastica\Request
     */
    protected $_lastRequest;

    /**
     * @var \Elastica\Response
     */
    protected $_lastResponse;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * If the updateDocument call should retry on conflicts.
     *
     * @var bool
     */
    private $_retryOnConflict = false;

    /**
     * Creates a new Elastica client.
     *
     * @param ConnectionPoolInterface|array $config A ConnectionPoolInterface or $config array for configuring connections
     * @param callback $callback OPTIONAL Callback function which can be used to be notified about errors (for example connection down)
     * @param LoggerInterface $logger
     */
    public function __construct($config = array(), $callback = null, LoggerInterface $logger = null)
    {
        if (!$logger && isset($config['log']) && $config['log']) {
            $logger = new Log($config['log']);
        }
        $this->_logger = $logger ?: new NullLogger();

        if (!$config instanceof ConnectionPoolInterface) {
            $this->_retryOnConflict = isset($config['retryOnConflict']) ? $config['retryOnConflict'] : false;

            $builder = new PoolBuilder();
            $config = $builder->buildPool($config, $callback);
        }

        $this->_connectionPool = $config;
    }

    /**
     * Returns the index for the given connection.
     *
     * @param string $name Index name to create connection to
     *
     * @return \Elastica\Index Index for the given name
     */
    public function getIndex($name)
    {
        return new Index($this, $name);
    }

    /**
     * Adds a HTTP Header.
     *
     * @deprecated Use ConnectionPool::addHeader.
     *
     * @param string $header      The HTTP Header
     * @param string $headerValue The HTTP Header Value
     *
     * @throws \Elastica\Exception\InvalidException If $header or $headerValue is not a string
     *
     * @return $this
     */
    public function addHeader($header, $headerValue)
    {
        foreach ($this->_connectionPool->getConnections() as $connection) {
            $connection->addHeader($header, $headerValue);
        }

        return $this;
    }

    /**
     * Remove a HTTP Header.
     *
     * @deprecated Use ConnectionPool::removeHeader.
     *
     * @param string $header The HTTP Header to remove
     *
     * @throws \Elastica\Exception\InvalidException If $header is not a string
     *
     * @return $this
     */
    public function removeHeader($header)
    {
        foreach ($this->_connectionPool->getConnections() as $connection) {
            $connection->removeHeader($header);
        }

        return $this;
    }

    /**
     * Uses _bulk to send documents to the server.
     *
     * Array of \Elastica\Document as input. Index and type has to be
     * set inside the document, because for bulk settings documents,
     * documents can belong to any type and index
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/docs-bulk.html
     *
     * @param array|\Elastica\Document[] $docs Array of Elastica\Document
     *
     * @throws \Elastica\Exception\InvalidException If docs is empty
     *
     * @return \Elastica\Bulk\ResponseSet Response object
     */
    public function updateDocuments(array $docs)
    {
        if (empty($docs)) {
            throw new InvalidException('Array has to consist of at least one element');
        }

        $bulk = new Bulk($this);

        $bulk->addDocuments($docs, \Elastica\Bulk\Action::OP_TYPE_UPDATE);

        return $bulk->send();
    }

    /**
     * Uses _bulk to send documents to the server.
     *
     * Array of \Elastica\Document as input. Index and type has to be
     * set inside the document, because for bulk settings documents,
     * documents can belong to any type and index
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/docs-bulk.html
     *
     * @param array|\Elastica\Document[] $docs Array of Elastica\Document
     *
     * @throws \Elastica\Exception\InvalidException If docs is empty
     *
     * @return \Elastica\Bulk\ResponseSet Response object
     */
    public function addDocuments(array $docs)
    {
        if (empty($docs)) {
            throw new InvalidException('Array has to consist of at least one element');
        }

        $bulk = new Bulk($this);

        $bulk->addDocuments($docs);

        return $bulk->send();
    }

    /**
     * Update document, using update script. Requires elasticsearch >= 0.19.0.
     *
     * @param int                                                      $id      document id
     * @param array|\Elastica\Script\AbstractScript|\Elastica\Document $data    raw data for request body
     * @param string                                                   $index   index to update
     * @param string                                                   $type    type of index to update
     * @param array                                                    $options array of query params to use for query. For possible options check es api
     *
     * @return \Elastica\Response
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/docs-update.html
     */
    public function updateDocument($id, $data, $index, $type, array $options = array())
    {
        $path = $index.'/'.$type.'/'.$id.'/_update';

        if ($data instanceof AbstractScript) {
            $requestData = $data->toArray();
        } elseif ($data instanceof Document) {
            $requestData = array('doc' => $data->getData());

            if ($data->getDocAsUpsert()) {
                $requestData['doc_as_upsert'] = true;
            }

            $docOptions = $data->getOptions(
                array(
                    'version',
                    'version_type',
                    'routing',
                    'percolate',
                    'parent',
                    'fields',
                    'retry_on_conflict',
                    'consistency',
                    'replication',
                    'refresh',
                    'timeout',
                )
            );
            $options += $docOptions;
            // set fields param to source only if options was not set before
            if ($data instanceof Document
                && ($data->isAutoPopulate() || $this->_autoPopulate)
                && !isset($options['fields'])
            ) {
                $options['fields'] = '_source';
            }
        } else {
            $requestData = $data;
        }

        //If an upsert document exists
        if ($data instanceof AbstractScript || $data instanceof Document) {
            if ($data->hasUpsert()) {
                $requestData['upsert'] = $data->getUpsert()->getData();
            }
        }

        if (!isset($options['retry_on_conflict'])) {
            $options['retry_on_conflict'] = $this->_retryOnConflict;
        }

        $response = $this->request($path, Request::POST, $requestData, $options);

        if ($response->isOk()
            && $data instanceof Document
            && ($data->isAutoPopulate() || $this->_autoPopulate)
        ) {
            $responseData = $response->getData();
            if (isset($responseData['_version'])) {
                $data->setVersion($responseData['_version']);
            }
            if (isset($options['fields'])) {
                $this->_populateDocumentFieldsFromResponse($response, $data, $options['fields']);
            }
        }

        return $response;
    }

    /**
     * @param \Elastica\Response $response
     * @param \Elastica\Document $document
     * @param string             $fields   Array of field names to be populated or '_source' if whole document data should be updated
     */
    protected function _populateDocumentFieldsFromResponse(Response $response, Document $document, $fields)
    {
        $responseData = $response->getData();
        if ('_source' == $fields) {
            if (isset($responseData['get']['_source']) && is_array($responseData['get']['_source'])) {
                $document->setData($responseData['get']['_source']);
            }
        } else {
            $keys = explode(',', $fields);
            $data = $document->getData();
            foreach ($keys as $key) {
                if (isset($responseData['get']['fields'][$key])) {
                    $data[$key] = $responseData['get']['fields'][$key];
                } elseif (isset($data[$key])) {
                    unset($data[$key]);
                }
            }
            $document->setData($data);
        }
    }

    /**
     * Bulk deletes documents.
     *
     * @param array|\Elastica\Document[] $docs
     *
     * @throws \Elastica\Exception\InvalidException
     *
     * @return \Elastica\Bulk\ResponseSet
     */
    public function deleteDocuments(array $docs)
    {
        if (empty($docs)) {
            throw new InvalidException('Array has to consist of at least one element');
        }

        $bulk = new Bulk($this);
        $bulk->addDocuments($docs, Action::OP_TYPE_DELETE);

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
     * Establishes the client connections
     *
     * @deprecated
     */
    public function connect()
    {
        // noop
    }

    /**
     * @deprecated Use $client->getConnectionPool()->addConnection()
     *
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
     * @deprecated Use $client->getConnectionPool()->hasConnection()
     *
     * Determines whether a valid connection is available for use.
     *
     * @return bool
     */
    public function hasConnection()
    {
        return $this->_connectionPool->hasConnection();
    }

    /**
     * @deprecated Use $client->getConnectionPool()->getConnection()
     *
     * @throws \Elastica\Exception\ClientException
     *
     * @return \Elastica\Connection
     */
    public function getConnection()
    {
        return $this->_connectionPool->getConnection();
    }

    /**
     * @deprecated Use $client->getConnectionPool()->getConnections()
     *
     * @return \Elastica\Connection[]
     */
    public function getConnections()
    {
        return $this->_connectionPool->getConnections();
    }

    /**
     * @deprecated Use $client->getConnectionPool()->getConnectionStrategy()
     *
     * @return \Elastica\Connection\Strategy\StrategyInterface
     */
    public function getConnectionStrategy()
    {
        return $this->_connectionPool->getStrategy();
    }

    /**
     * @deprecated Use $client->getConnectionPool()->setConnections()
     *
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
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/docs-bulk.html
     *
     * @param array                  $ids     Document ids
     * @param string|\Elastica\Index $index   Index name
     * @param string|\Elastica\Type  $type    Type of documents
     * @param string|bool            $routing Optional routing key for all ids
     *
     * @throws \Elastica\Exception\InvalidException
     *
     * @return \Elastica\Bulk\ResponseSet Response  object
     */
    public function deleteIds(array $ids, $index, $type, $routing = false)
    {
        if (empty($ids)) {
            throw new InvalidException('Array has to consist of at least one id');
        }

        $bulk = new Bulk($this);
        $bulk->setIndex($index);
        $bulk->setType($type);

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
     *         array('index' => array('_index' => 'test', '_type' => 'user', '_id' => '1')),
     *         array('user' => array('name' => 'hans')),
     *         array('delete' => array('_index' => 'test', '_type' => 'user', '_id' => '2'))
     * );
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/docs-bulk.html
     *
     * @param array $params Parameter array
     *
     * @throws \Elastica\Exception\ResponseException
     * @throws \Elastica\Exception\InvalidException
     *
     * @return \Elastica\Bulk\ResponseSet Response object
     */
    public function bulk(array $params)
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
     * @param string $path   Path to call
     * @param string $method Rest method to use (GET, POST, DELETE, PUT)
     * @param array  $data   OPTIONAL Arguments as array
     * @param array  $query  OPTIONAL Query params
     *
     * @throws Exception\ConnectionException|\Exception
     *
     * @return Response Response object
     */
    public function request($path, $method = Request::GET, $data = array(), array $query = array())
    {
        $connection = $this->_connectionPool->getConnection();
        $request = $this->_lastRequest = new Request($path, $method, $data, $query, $connection);
        $this->_lastResponse = null;

        try {
            $response = $this->_lastResponse = $request->send();
        } catch (ConnectionException $e) {
            $this->_connectionPool->onFail($connection, $e, $this);

            $this->_log($e);

            // In case there is no valid connection left, throw exception which caused the disabling of the connection.
            if (!$this->hasConnection()) {
                throw $e;
            }

            return $this->request($path, $method, $data, $query);
        }

        $this->_log($request);

        return $response;
    }

    /**
     * logging.
     *
     * @deprecated Overwriting Client->_log is deprecated. Handle logging functionality by using a custom LoggerInterface.
     *
     * @param mixed $context
     */
    protected function _log($context)
    {
        if ($context instanceof ConnectionException) {
            $this->_logger->error('Elastica Request Failure', [
                'exception' => $context,
                'request' => $context->getRequest()->toArray(),
                'retry' => $this->hasConnection()
            ]);

            return;
        }

        if ($context instanceof Request) {
            $this->_logger->debug('Elastica Request', [
                'request' => $context->toArray(),
                'response' => $this->_lastResponse ? $this->_lastResponse->getData() : null,
                'responseStatus' => $this->_lastResponse ? $this->_lastResponse->getStatus() : null
            ]);

            return;
        }

        $this->_logger->debug('Elastica Request', [
            'message' => $context
        ]);
    }

    /**
     * Optimizes all search indices.
     *
     * @param array $args OPTIONAL Optional arguments
     *
     * @return \Elastica\Response Response object
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-optimize.html
     */
    public function optimizeAll($args = array())
    {
        return $this->request('_optimize', Request::POST, array(), $args);
    }

    /**
     * Refreshes all search indices.
     *
     * @return \Elastica\Response Response object
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-refresh.html
     */
    public function refreshAll()
    {
        return $this->request('_refresh', Request::POST);
    }

    /**
     * @return Request
     */
    public function getLastRequest()
    {
        return $this->_lastRequest;
    }

    /**
     * @return Response
     */
    public function getLastResponse()
    {
        return $this->_lastResponse;
    }

    /**
     * Replace the existing logger.
     *
     * @param LoggerInterface $logger
     *
     * @return $this
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->_logger = $logger;

        return $this;
    }
}
