<?php

namespace Elastica;

use Elastica\Exception\BulkResponseException;
use Elastica\Exception\ClientException;
use Elastica\Exception\ConnectionException;
use Elastica\Exception\InvalidException;
use Elastica\Exception\NotImplementedException;

/**
 * Client to connect the the elasticsearch server
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class Client
{
    /**
     * Config with defaults
     *
     * log: Set to true, to enable logging, set a string to log to a specific file
     * retryOnConflict: Use in \Elastica\Client::updateDocument
     *
     * @var array
     */
    protected $_config = array(
        'host'            => null,
        'port'            => null,
        'path'            => null,
        'url'             => null,
        'transport'       => null,
        'persistent'      => true,
        'timeout'         => null,
        'connections'     => array(),	// host, port, path, timeout, transport, persistent, timeout, config -> (curl, headers, url)
        'roundRobin'      => false,
        'log'             => false,
        'retryOnConflict' => 0,
    );

    /**
     * @var \Elastica\Connection[] List of connections
     */
    protected $_connections = array();

    /**
     * @var callback
     */
    protected $_callback = null;

    /**
     * @var \Elastica\Request
     */
    protected $_lastRequest;

    /**
     * @var \Elastica\Response
     */
    protected $_lastResponse;

    /**
     * Creates a new Elastica client
     *
     * @param array    $config   OPTIONAL Additional config options
     * @param callback $callback OPTIONAL Callback function which can be used to be notified about errors (for example connection down)
     */
    public function __construct(array $config = array(), $callback = null)
    {
        $this->setConfig($config);
        $this->_callback = $callback;
        $this->_initConnections();
    }

    /**
     * Inits the client connections
     */
    protected function _initConnections()
    {
        $connections = $this->getConfig('connections');

        foreach ($connections as $connection) {
            $this->_connections[] = Connection::create($connection);
        }

        if (isset($_config['servers'])) {
            $this->_connections[] = Connection::create($this->getConfig('servers'));
        }

        // If no connections set, create default connection
        if (empty($this->_connections)) {
            $this->_connections[] = Connection::create($this->_configureParams());
        }
    }

    /**
     * @return array $params
     */
    protected function _configureParams()
    {
        $config = $this->getConfig();

        $params = array();
        $params['config'] = array();
        foreach ($config as $key => $value) {
            if (in_array($key, array('curl', 'headers', 'url'))) {
                $params['config'][$key] = $value;
            } else {
                $params[$key] = $value;
            }
        }

        return $params;
    }

    /**
     * Sets specific config values (updates and keeps default values)
     *
     * @param  array           $config Params
     * @return \Elastica\Client
     */
    public function setConfig(array $config)
    {
        foreach ($config as $key => $value) {
            $this->_config[$key] = $value;
        }

        return $this;
    }

    /**
     * Returns a specific config key or the whole
     * config array if not set
     *
     * @param  string                              $key Config key
     * @throws \Elastica\Exception\InvalidException
     * @return array|string                        Config value
     */
    public function getConfig($key = '')
    {
        if (empty($key)) {
            return $this->_config;
        }

        if (!array_key_exists($key, $this->_config)) {
            throw new InvalidException('Config key is not set: ' . $key);
        }

        return $this->_config[$key];
    }

    /**
     * Sets / overwrites a specific config value
     *
     * @param  string          $key   Key to set
     * @param  mixed           $value Value
     * @return \Elastica\Client Client object
     */
    public function setConfigValue($key, $value)
    {
        return $this->setConfig(array($key => $value));
    }

    /**
     * @param string|array $key config key or path to config key
     * @param mixed $default default value will be returned if key was not found
     */
    public function getConfigValue($keys, $default = null)
    {
        $value = $this->_config;
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
     * Returns the index for the given connection
     *
     * @param  string         $name Index name to create connection to
     * @return \Elastica\Index Index for the given name
     */
    public function getIndex($name)
    {
        return new Index($this, $name);
    }

    /**
     * Adds a HTTP Header
     *
     * @param  string                              $header      The HTTP Header
     * @param  string                              $headerValue The HTTP Header Value
     * @throws \Elastica\Exception\InvalidException If $header or $headerValue is not a string
     */
    public function addHeader($header, $headerValue)
    {
        if (is_string($header) && is_string($headerValue)) {
            $this->_config['headers'][$header] = $headerValue;
        } else {
            throw new InvalidException('Header must be a string');
        }
    }

    /**
     * Remove a HTTP Header
     *
     * @param  string                              $header The HTTP Header to remove
     * @throws \Elastica\Exception\InvalidException IF $header is not a string
     */
    public function removeHeader($header)
    {
        if (is_string($header)) {
            if (array_key_exists($header, $this->_config['headers'])) {
                unset($this->_config['headers'][$header]);
            }
        } else {
            throw new InvalidException('Header must be a string');
        }
    }

    /**
     * Uses _bulk to send documents to the server
     *
     * Array of \Elastica\Document as input. Index and type has to be
     * set inside the document, because for bulk settings documents,
     * documents can belong to any type and index
     *
     * @param  array|\Elastica\Document[]           $docs Array of Elastica\Document
     * @return \Elastica\Response                   Response object
     * @throws \Elastica\Exception\InvalidException If docs is empty
     * @link http://www.elasticsearch.org/guide/reference/api/bulk.html
     */
    public function addDocuments(array $docs)
    {
        if (empty($docs)) {
            throw new InvalidException('Array has to consist of at least one element');
        }
        $params = array();

        foreach ($docs as $doc) {
            $params[] = array('index' => $doc->getOptions(
                array(
                    'index',
                    'type',
                    'id',
                    'version',
                    'version_type',
                    'routing',
                    'percolate',
                    'parent',
                    'ttl',
                    'timestamp',
                ),
                true
            ));
            $params[] = $doc->getData();
        }

        $response = $this->bulk($params);

        $this->_setDocumentIdsFromResponse($response, $docs);

        return $response;
    }

    /**
     * @param \Elastica\Response $response
     * @param \Elastica\Document[] $docs
     */
    protected function _setDocumentIdsFromResponse(Response $response, array $docs)
    {
        $data = $response->getData();
        /* @var Document $document */
        $document = reset($docs);
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                if (false === $document) {
                    break;
                }
                if ($document->isAutoPopulate()
                    || $this->getConfigValue(array('document', 'autoPopulate'), false)
                ) {
                    $opType = key($item);
                    $data = reset($item);
                    if (!$document->hasId() && 'create' == $opType && isset($data['_id'])) {
                        $document->setId($data['_id']);
                    }
                    if (isset($data['_version'])) {
                        $document->setVersion($data['_version']);
                    }
                }
                $document = next($docs);
            }
        }
    }

    /**
     * Update document, using update script. Requires elasticsearch >= 0.19.0
     *
     * @param  int                  $id      document id
     * @param  array|\Elastica\Script|\Elastica\Document $data    raw data for request body
     * @param  string               $index   index to update
     * @param  string               $type    type of index to update
     * @param  array                $options array of query params to use for query. For possible options check es api
     * @return \Elastica\Response
     * @link http://www.elasticsearch.org/guide/reference/api/update.html
     */
    public function updateDocument($id, $data, $index, $type, array $options = array())
    {
        $path =  $index . '/' . $type . '/' . $id . '/_update';

        if ($data instanceof Script) {
            $requestData = $data->toArray();
        } elseif ($data instanceof Document) {
            if ($data->hasScript()) {
                $requestData = $data->getScript()->toArray();
                $documentData = $data->getData();
                if (!empty($documentData)) {
                    $requestData['upsert'] = $documentData;
                }
            } else {
                $requestData = array('doc' => $data->getData());
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
            if (($data->isAutoPopulate()
                || $this->getConfigValue(array('document', 'autoPopulate'), false))
                && !isset($options['fields'])
            ) {
                $options['fields'] = '_source';
            }
        } else {
            $requestData = $data;
        }

        if (!isset($options['retry_on_conflict'])) {
            $retryOnConflict = $this->getConfig("retryOnConflict");
            $options['retry_on_conflict'] = $retryOnConflict;
        }

        $response = $this->request($path, Request::POST, $requestData, $options);

        if ($response->isOk()
            && $data instanceof Document
            && ($data->isAutoPopulate() || $this->getConfigValue(array('document', 'autoPopulate'), false))
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
     * @param string $fields Array of field names to be populated or '_source' if whole document data should be updated
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
     * Bulk deletes documents (not implemented yet)
     *
     * @param  array                                      $docs Docs
     * @throws \Elastica\Exception\NotImplementedException
     */
    public function deleteDocuments(array $docs)
    {
        // TODO: similar to delete ids but with type and index inside files
        throw new NotImplementedException('not implemented yet');
    }

    /**
     * Returns the status object for all indices
     *
     * @return \Elastica\Status Status object
     */
    public function getStatus()
    {
        return new Status($this);
    }

    /**
     * Returns the current cluster
     *
     * @return \Elastica\Cluster Cluster object
     */
    public function getCluster()
    {
        return new Cluster($this);
    }

    /**
     * @param  \Elastica\Connection $connection
     * @return \Elastica\Client
     */
    public function addConnection(Connection $connection)
    {
        $this->_connections[] = $connection;

        return $this;
    }

    /**
     * @throws \Elastica\Exception\ClientException
     * @return \Elastica\Connection
     */
    public function getConnection()
    {
        $enabledConnection = null;

        // TODO: Choose one after other if roundRobin -> should we shuffle the array?
        foreach ($this->_connections as $connection) {
            if ($connection->isEnabled()) {
                $enabledConnection = $connection;
            }
        }

        if (!$enabledConnection) {
            throw new ClientException('No enabled connection');
        }

        return $enabledConnection;
    }

    /**
     * @return \Elastica\Connection[]
     */
    public function getConnections()
    {
        return $this->_connections;
    }

    /**
     * @param  \Elastica\Connection[] $connections
     * @return \Elastica\Client
     */
    public function setConnections(array $connections)
    {
        $this->_connections = $connections;

        return $this;
    }

    /**
     * Deletes documents with the given ids, index, type from the index
     *
     * @param  array                               $ids   Document ids
     * @param  string|\Elastica\Index               $index Index name
     * @param  string|\Elastica\Type                $type  Type of documents
     * @throws \Elastica\Exception\InvalidException
     * @return \Elastica\Response                   Response object
     * @link http://www.elasticsearch.org/guide/reference/api/bulk.html
     */
    public function deleteIds(array $ids, $index, $type)
    {
        if (empty($ids)) {
            throw new InvalidException('Array has to consist of at least one id');
        }

        if ($index instanceof Index) {
            $index = $index->getName();
        }

        if ($type instanceof Type) {
            $type = $type->getName();
        }

        $params = array();
        foreach ($ids as $id) {
            $action = array(
                'delete' => array(
                    '_index' => $index,
                    '_type' => $type,
                    '_id' => $id,
                )
            );

            $params[] = $action;
        }

        return $this->bulk($params);
    }

    /**
     * Bulk operation
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
     * @param  array                                    $params Parameter array
     * @throws \Elastica\Exception\BulkResponseException
     * @throws \Elastica\Exception\InvalidException
     * @return \Elastica\Response                        Response object
     * @todo Test
     * @link http://www.elasticsearch.org/guide/reference/api/bulk.html
     */
    public function bulk(array $params)
    {
        if (empty($params)) {
            throw new InvalidException('Array has to consist of at least one param');
        }

        $path = '_bulk';

        $queryString = '';
        foreach ($params as $baseArray) {
            // Always newline needed
            $queryString .= json_encode($baseArray) . PHP_EOL;
        }

        $response = $this->request($path, Request::PUT, $queryString);
        $data = $response->getData();

        if (isset($data['items'])) {
            foreach ($data['items'] as $item) {
                $params = reset($item);
                if (isset($params['error'])) {
                    throw new BulkResponseException($response);
                }
            }
        }

        return $response;
    }

    /**
     * Makes calls to the elasticsearch server based on this index
     *
     * It's possible to make any REST query directly over this method
     *
     * @param  string            $path   Path to call
     * @param  string            $method Rest method to use (GET, POST, DELETE, PUT)
     * @param  array             $data   OPTIONAL Arguments as array
     * @param  array             $query  OPTIONAL Query params
     * @return \Elastica\Response Response object
     */
    public function request($path, $method = Request::GET, $data = array(), array $query = array())
    {
        $connection = $this->getConnection();
        try {
            $request = new Request($path, $method, $data, $query, $connection);

            $this->_log($request);

            $response = $request->send();

            $this->_lastRequest = $request;
            $this->_lastResponse = $response;

            return $response;

        } catch (ConnectionException $e) {
            $connection->setEnabled(false);

            // Calls callback with connection as param to make it possible to persist invalid connections
            if ($this->_callback) {
                call_user_func($this->_callback, $connection);
            }

            return $this->request($path, $method, $data, $query);
        }
    }

    /**
     * Optimizes all search indices
     *
     * @param  array             $args OPTIONAL Optional arguments
     * @return \Elastica\Response Response object
     * @link http://www.elasticsearch.org/guide/reference/api/admin-indices-optimize.html
     */
    public function optimizeAll($args = array())
    {
        return $this->request('_optimize', Request::POST, $args);
    }

    /**
     * @param string|\Elastica\Request $message
     */
    protected function _log($message)
    {
        if ($this->getConfig('log')) {
            $log = new Log($this->getConfig('log'));
            $log->log($message);
        }
    }

    /**
     * @return \Elastica\Request
     */
    public function getLastRequest()
    {
        return $this->_lastRequest;
    }

    /**
     * @return \Elastica\Response
     */
    public function getLastResponse()
    {
        return $this->_lastResponse;
    }
}
