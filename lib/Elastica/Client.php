<?php
/**
 * Client to connect the the elasticsearch server
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class Elastica_Client
{
    /**
     * Config with defaults
     *
     * log: Set to true, to enable logging, set a string to log to a specific file
     * retryOnConflict: Use in Elastica_Client::updateDocument
     *
     * @var array
     */
    protected $_config = array(
        'host' => null,
        'port' => null,
        'path' => null,
        'url' => null,
        'transport' => null,
        'persistent' => true,
        'timeout' => null,
        'connections' => array(),	// host, port, path, timeout, transport, persistent, timeout, config -> (curl, headers, url)
        'roundRobin' => false,
        'log' => false,
        'retryOnConflict' => 0,
    );

    /**
     * @var Elastica_Connection[] List of connections
     */
    protected $_connections = array();

    /**
     * @var callback
     */
    protected $_callback = null;

    /**
     * Creates a new Elastica client
     *
     * @param array $config OPTIONAL Additional config options
     * @param callback $callback OPTIONAL Callback function which can be used to be notified about errors (for example conenction down)
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
            $this->_connections[] = Elastica_Connection::create($connection);
        }

        if (isset($_config['servers'])) {
            $this->_connections[] = Elastica_Connection::create($this->getConfig('servers'));
        }

        // If no connections set, create default connection
        if (empty($this->_connections)) {
            $this->_connections[] = Elastica_Connection::create($this->_configureParams());
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
     * @param array $config Params
     * @return Elastica_Client
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
     * @param  string       $key Config key
     * @throws Elastica_Exception_Invalid
     * @return array|string Config value
     */
    public function getConfig($key = '')
    {
        if (empty($key)) {
            return $this->_config;
        }

        if (!array_key_exists($key, $this->_config)) {
            throw new Elastica_Exception_Invalid('Config key is not set: ' . $key);
        }

        return $this->_config[$key];
    }

    /**
     * Sets / overwrites a specific config value
     *
     * @param  string          $key   Key to set
     * @param  mixed           $value Value
     * @return Elastica_Client Client object
     */
    public function setConfigValue($key, $value)
    {
        return $this->setConfig(array($key => $value));
    }

    /**
     * Returns the index for the given connection
     *
     * @param  string         $name Index name to create connection to
     * @return Elastica_Index Index for the given name
     */
    public function getIndex($name)
    {
        return new Elastica_Index($this, $name);
    }

    /**
     * Adds a HTTP Header
     *
     * @param  string                     $header      The HTTP Header
     * @param  string                     $headerValue The HTTP Header Value
     * @throws Elastica_Exception_Invalid If $header or $headerValue is not a string
     */
    public function addHeader($header, $headerValue)
    {
        if (is_string($header) && is_string($headerValue)) {
            $this->_config['headers'][$header] = $headerValue;
        } else {
            throw new Elastica_Exception_Invalid('Header must be a string');
        }
    }

    /**
     * Remove a HTTP Header
     *
     * @param  string                     $header The HTTP Header to remove
     * @throws Elastica_Exception_Invalid IF $header is not a string
     */
    public function removeHeader($header)
    {
        if (is_string($header)) {
            if (array_key_exists($header, $this->_config['headers'])) {
                unset($this->_config['headers'][$header]);
            }
        } else {
            throw new Elastica_Exception_Invalid('Header must be a string');
        }
    }

    /**
     * Uses _bulk to send documents to the server
     *
     * Array of Elastica_Document as input. Index and type has to be
     * set inside the document, because for bulk settings documents,
     * documents can belong to any type and index
     *
     * @param  array|Elastica_Document[]  $docs Array of Elastica_Document
     * @return Elastica_Response          Response object
     * @throws Elastica_Exception_Invalid If docs is empty
     * @link http://www.elasticsearch.org/guide/reference/api/bulk.html
     */
    public function addDocuments(array $docs)
    {
        if (empty($docs)) {
            throw new Elastica_Exception_Invalid('Array has to consist of at least one element');
        }
        $params = array();

        foreach ($docs as $doc) {
            $params[] = array('index' => $doc->getParams());
            $params[] = $doc->getData();
        }

        return $this->bulk($params);
    }

    /**
     * Update document, using update script. Requires elasticsearch >= 0.19.0
     *
     * @param  int               $id      document id
     * @param  Elastica_Script   $script  script to use for update
     * @param  string            $index   index to update
     * @param  string            $type    type of index to update
     * @param  array             $options array of query params to use for query. For possible options check es api
     * @return Elastica_Response
     * @link http://www.elasticsearch.org/guide/reference/api/update.html
     */
    public function updateDocument($id, Elastica_Script $script, $index, $type, array $options = array())
    {
        $path =  $index . '/' . $type . '/' . $id . '/_update';
        if (!isset($options['retry_on_conflict'])) {
            $retryOnConflict = $this->getConfig("retryOnConflict");
            $options['retry_on_conflict'] = $retryOnConflict;
        }

        $data = array(
            'script' => $script->getScript(),
        );
        if ($script->getLang() != null) {
            $data['lang'] = $script->getLang();
        }
        if ($script->getParams() != null) {
            $data['params'] = $script->getParams();
        }

        return $this->request($path, Elastica_Request::POST, $data, $options);
    }

    /**
     * Bulk deletes documents (not implemented yet)
     *
     * @param  array              $docs Docs
     * @throws Elastica_Exception_NotImplemented
     */
    public function deleteDocuments(array $docs)
    {
        // TODO: similar to delete ids but with type and index inside files
        throw new Elastica_Exception_NotImplemented('not implemented yet');
    }

    /**
     * Returns the status object for all indices
     *
     * @return Elastica_Status Status object
     */
    public function getStatus()
    {
        return new Elastica_Status($this);
    }

    /**
     * Returns the current cluster
     *
     * @return Elastica_Cluster Cluster object
     */
    public function getCluster()
    {
        return new Elastica_Cluster($this);
    }

    /**
     * @param Elastica_Connection $connection
     * @return Elastica_Client
     */
    public function addConnection(Elastica_Connection $connection)
    {
        $this->_connections[] = $connection;
        return $this;
    }

    /**
     * @return Elastica_Connection
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
            throw new Elastica_Exception_Client('No enabled connection');
        }

        return $enabledConnection;
    }

    /**
     * @return Elastica_Connection[]
     */
    public function getConnections()
    {
        return $this->_connections;
    }

    /**
     * @param Elastica_Connection[] $connections
     * @return Elastica_Client
     */
    public function setConnections(array $connections)
    {
        $this->_connections = $connections;
        return $this;
    }

    /**
     * Deletes documents with the given ids, index, type from the index
     *
     * @param  array                 $ids   Document ids
     * @param  string|Elastica_Index $index Index name
     * @param  string|Elastica_Type  $type  Type of documents
     * @throws Elastica_Exception_Invalid
     * @return Elastica_Response     Response object
     * @link http://www.elasticsearch.org/guide/reference/api/bulk.html
     */
    public function deleteIds(array $ids, $index, $type)
    {
        if (empty($ids)) {
            throw new Elastica_Exception_Invalid('Array has to consist of at least one id');
        }

        if ($index instanceof Elastica_Index) {
            $index = $index->getName();
        }

        if ($type instanceof Elastica_Type) {
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
     * @param  array             $params Parameter array
     * @throws Elastica_Exception_BulkResponse
     * @throws Elastica_Exception_Invalid
     * @return Elastica_Response Response object
     * @todo Test
     * @link http://www.elasticsearch.org/guide/reference/api/bulk.html
     */
    public function bulk(array $params)
    {
        if (empty($params)) {
            throw new Elastica_Exception_Invalid('Array has to consist of at least one param');
        }

        $path = '_bulk';

        $queryString = '';
        foreach ($params as $baseArray) {
            // Always newline needed
            $queryString .= json_encode($baseArray) . PHP_EOL;
        }

        $response = $this->request($path, Elastica_Request::PUT, $queryString);
        $data = $response->getData();

        if (isset($data['items'])) {
            foreach ($data['items'] as $item) {
                $params = reset($item);
                if (isset($params['error'])) {
                    throw new Elastica_Exception_BulkResponse($response);
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
     * @return Elastica_Response Response object
     */
    public function request($path, $method = Elastica_Request::GET, $data = array(), array $query = array())
    {
        $connection = $this->getConnection();
        try {
            $request = new Elastica_Request($path, $method, $data, $query, $connection);

            if ($this->getConfig('log')) {
                $log = new Elastica_Log($this->getConfig('log'));
                $log->log($request);
            }

            return $request->send();
        } catch (Elastica_Exception_Connection $e) {
            $connection->setEnabled(false);

            // Calls callback with connection as param to make it possible to persist invalid conenctions
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
     * @return Elastica_Response Response object
     * @link http://www.elasticsearch.org/guide/reference/api/admin-indices-optimize.html
     */
    public function optimizeAll($args = array())
    {
        return $this->request('_optimize', Elastica_Request::POST, $args);
    }
}
