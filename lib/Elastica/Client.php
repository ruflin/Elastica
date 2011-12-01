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
	 * Default elastic search port
	 */
	const DEFAULT_PORT = 9200;

	/**
	 * Default host
	 */
	const DEFAULT_HOST = 'localhost';

	/**
	 * Default transport
	 *
	 * @var string
	 */
	const DEFAULT_TRANSPORT = 'Http';

	/**
	 * Number of seconds after a timeout occurs for every request
	 * If using indexing of file large value necessary.
	 */
	const TIMEOUT = 300;

	/**
	 * Config with defaults
	 *
	 * @var array
	 */
	protected $_config = array(
		'host' => self::DEFAULT_HOST,
		'port' => self::DEFAULT_PORT,
		'transport' => self::DEFAULT_TRANSPORT,
		'timeout' => self::TIMEOUT,
		'headers' => array(),
		'servers' => array(),
		'roundRobin' => false,
		'log' => false,
	);

	/**
	 * Creates a new Elastica client
	 *
	 * @param array $config OPTIONAL Additional config options
	 */
	public function __construct(array $config = array()) {
		$this->setConfig($config);
	}

	/**
	 * Sets specific config values (updates and keeps default values)
	 *
	 * @param array $config Params
	 */
	public function setConfig(array $config) {
		foreach ($config as $key => $value) {
			$this->_config[$key] = $value;
		}

		return $this;
	}

	/**
	 * Returns a specific config key or the whole
	 * config array if not set
	 *
	 * @param string $key Config key
	 * @return array|string Config value
	 */
	public function getConfig($key = '') {
		if (empty($key)) {
			return $this->_config;
		}

		if (isset($this->_config[$key])) {
			return $this->_config[$key];
		}

		return $this->_config;
	}

	/**
	 * Sets / overwrites a specific config value
	 *
	 * @param string $key Key to set
	 * @param mixed $value Value
	 * @return Elastica_Client Client object
	 */
	public function setConfigValue($key, $value) {
		return $this->setConfig(array($key => $value));
	}

	/**
	 * Returns the index for the given connection
	 *
	 * @param string $name Index name to create connection to
	 * @return Elastica_Index Index for the given name
	 */
	public function getIndex($name) {
		return new Elastica_Index($this, $name);
	}

	/**
	 * Returns host the client connects to
	 *
	 * @return string Host
	 */
	public function getHost() {
		return $this->getConfig('host');
	}

	/**
	 * Returns connection port of this client
	 *
	 * @return int Connection port
	 */
	public function getPort() {
		return (int) $this->getConfig('port');
	}

	/**
	 * Returns transport type to user
	 *
	 * @return string Transport type
	 */
	public function getTransport() {
		return $this->getConfig('transport');
	}

	/**
	 * Adds a HTTP Header
	 *
	 * @param string $header The HTTP Header
	 * @param string $headerValue The HTTP Header Value
	 * @throws Elastica_Exception_Invalid If $header or $headerValue is not a string
	 */
	public function addHeader($header, $headerValue) {
		if (is_string($header) && is_string($headerValue)) {
			$this->_config['headers'][$header] = $headerValue;
		} else {
			throw new Elastica_Exception_Invalid('Header must be a string');
		}
	}

	/**
	 * Remove a HTTP Header
	 *
	 * @param string $header The HTTP Header to remove
	 * @throws Elastica_Exception_Invalid IF $header is not a string
	 */
	public function removeHeader($header) {
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
	 * @param array $docs Array of Elastica_Document
	 * @return Elastica_Response Response object
	 * @throws Elastica_Exception_Invalid If docs is empty
	 * @link http://www.elasticsearch.com/docs/elasticsearch/rest_api/bulk/
	 */
	public function addDocuments(array $docs) {

		if (empty($docs)) {
			throw new Elastica_Exception_Invalid('Array has to consist of at least one element');
		}
		$params = array();

		foreach($docs as $doc) {
			
			$indexInfo = array(
				'_index' => $doc->getIndex(),
				'_type' => $doc->getType(),
				'_id' => $doc->getId()
			);
			
			$version = $doc->getVersion();
			if (!empty($version)) {
				$indexInfo['_version'] = $version;
			}
			
			$parent = $doc->getParent();
			if (!empty($parent)) {
				$indexInfo['_parent'] = $parent;
			}

			$params[] = array('index' => $indexInfo);
			$params[] = $doc->getData();
		}
		return $this->bulk($params);
	}

	/**
	 * Bulk deletes documents (not implemented yet)
	 *
	 * @param array $docs Docs
	 * @throws Elastica_Exception
	 */
	public function deleteDocuments(array $docs) {
		// TODO: similar to delete ids but with type and index inside files
		throw new Elastica_Exception_NotImplemented('not implemented yet');
	}

	/**
	 * Returns the status object for all indices
	 *
	 * @return Elastica_Status Status object
	 */
	public function getStatus() {
		return new Elastica_Status($this);
	}

	/**
	 * Returns the current cluster
	 *
	 * @return Elastica_Cluster Cluster object
	 */
	public function getCluster() {
		return new Elastica_Cluster($this);
	}

	/**
	 * Deletes documents with the given ids, index, type from the index
	 *
	 * @param array $ids Document ids
	 * @param string|Elastica_Index $index Index name
	 * @param string|Elastica_Type $type Type of documents
	 * @return Elastica_Response Response object
	 * @throws Elastica_Exception If ids is empty
	 * @link http://www.elasticsearch.com/docs/elasticsearch/rest_api/bulk/
	 */
	public function deleteIds(array $ids, $index, $type) {
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
		foreach($ids as $id) {
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
	 * 		array('index' => array('_index' => 'test', '_type' => 'user', '_id' => '1')),
	 * 		array('user' => array('name' => 'hans')),
	 * 		array('delete' => array('_index' => 'test', '_type' => 'user', '_id' => '2'))
	 * );
	 *
	 * @param array $params Parameter array
	 * @return Elastica_Response Reponse object
	 * @todo Test
	 * @link http://www.elasticsearch.com/docs/elasticsearch/rest_api/bulk/
	 */
	public function bulk(array $params) {
		if (empty($params)) {
			throw new Elastica_Exception_Invalid('Array has to consist of at least one param');
		}

		$path = '_bulk';

		$queryString = '';
		foreach($params as $index => $baseArray) {
			// Always newline needed
			$queryString .= json_encode($baseArray) . PHP_EOL;
		}

		$response = $this->request($path, Elastica_Request::PUT, $queryString);
		$data = $response->getData();

		if (isset($data['items'])) {
			foreach($data['items'] as $item) {
				$params = reset($item);
				if(isset($params['error'])) {
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
	 * @param string $path Path to call
	 * @param string $method Rest method to use (GET, POST, DELETE, PUT)
	 * @param array $data OPTIONAL Arguments as array
	 * @return Elastica_Response Response object
	 */
	public function request($path, $method, $data = array()) {
		$request = new Elastica_Request($this, $path, $method, $data);
		return $request->send();
	}

	/**
	 * Optimizes all search indices
	 *
	 * @param array $args OPTIONAL Optional arguments
	 * @return Elastica_Response Response object
	 * @link http://www.elasticsearch.com/docs/elasticsearch/rest_api/admin/indices/optimize/
	 */
	public function optimizeAll($args = array()) {
		return $this->request('_optimize', Elastica_Request::POST, $args);
	}
}
