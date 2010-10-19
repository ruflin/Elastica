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
	 * Number of seconds after a timeout occurs for every request
	 * If using indexing of file large value necessary.
	 */
	const TIMEOUT = 300;

	/**
	 * Client host
	 *
	 * @var string Host
	 */
	protected $_host = self::DEFAULT_HOST;

	/**
	 * Client port
	 *
	 * @var int Port
	 */
	protected $_port = self::DEFAULT_PORT;

	/**
	 * Creates a new Elastica client
	 *
	 * @param string $host OPTIONAL Server host (default = self::DEFAULT_HOST)
	 * @param int $port OPTIONAL Port to connect (default = self::DEFAULT_PORT)
	 */
	public function __construct($host = self::DEFAULT_HOST, $port = self::DEFAULT_PORT) {
		$this->_host = $host;
		$this->_port = $port;
	}

	/**
	 * Returns the index for the given connection
	 *
	 * @param string $indexName Index name to create connection to
	 * @return Elastica_Index Index for the given name
	 */
	public function getIndex($indexName) {
		return new Elastica_Index($this, $indexName);
	}
	
	/**
	 * Returns host the client connects to
	 * 
	 * @return string Host
	 */
	public function getHost() {
		return $this->_host;
	}
	
	/**
	 * Returns connection port of this client
	 * 
	 * @return int Connection port
	 */
	public function getPort() {
		return intval($this->_port);
	}
	
	/**
	 * Uses _bulk to send documents to the server
	 * 
	 * Array of Elastica_Document as input. Index and type has to be
	 * set inside the document, because for bulk settings documents,
	 * documents can belong to any type and index
	 * 
	 * @link http://www.elasticsearch.com/docs/elasticsearch/rest_api/bulk/
	 * @param array $docs Array of Elastica_Document
	 */
	public function addDocuments(array $docs) {
		
		if (empty($docs)) {
			throw new Elastica_Exception('Array has to consist of at least one element');
		}
		$path = '_bulk';
		
		$queryString = '';
		foreach($docs as $doc) {
			$baseArray = array(
				'index' => array(
					'_index' => $doc->getIndex(),
					'_type' => $doc->getType(),
					'_id' => $doc->getId()
				)
			);
		
			// Always newline needed
			$queryString .= json_encode($baseArray) . PHP_EOL;
			
			$docArray = array(
				$doc->getType() => $doc->getData()
			);
			
			$queryString .= json_encode($docArray) . PHP_EOL;
		}
		
		return $this->request($path, Elastica_Request::PUT, $queryString);
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
		$request = new Elastica_Request($path, $method, $data);
		return $this->_callService($request);
	}
	
	/**
	 * Optimizes all search indexes
	 * 
	 * @link http://www.elasticsearch.com/docs/elasticsearch/rest_api/admin/indices/optimize/
	 * @return Elastica_Response Response object
	 */
	public function optimizeAll($args = array()) {
		return $this->request('_optimize', Elastica_Request::PUT, $args);
	}
	
	/**
	 * Makes calls to the elasticsearch server
	 * 
	 * All calls that are made to the server are down over this function
	 * 
	 * @param string $path Path to call
	 * @param string $method Rest method to use (GET, POST, DELETE, PUT)
	 * @param array $data OPTIONAL Arguments as array
	 * @return Elastica_Response Response object
	 */
	protected function _callService(Elastica_Request $request) {
		$conn = curl_init();
		$baseUri = 'http://' . $this->getHost() . ':' . $this->getPort() . '/';
		
		$baseUri .= $request->getPath();
		
		curl_setopt($conn, CURLOPT_URL, $baseUri);
		curl_setopt($conn, CURLOPT_TIMEOUT, self::TIMEOUT);
		curl_setopt($conn, CURLOPT_PORT, $this->getPort());
		curl_setopt($conn, CURLOPT_RETURNTRANSFER, 1) ;
		curl_setopt($conn, CURLOPT_CUSTOMREQUEST, $request->getMethod());
		
		// TODO: REFACTOR
		$data = $request->getData();
		
		if (!empty($data)) {
			if (is_array($data)) {
				$content = json_encode($data);
			} else {
				$content = $data;
			}			
			
			// Escaping of / not necessary. Causes problems in base64 encoding of files
			$content = str_replace('\/', '/', $content);
			curl_setopt($conn, CURLOPT_POSTFIELDS, $content);
		}
		
		$start = microtime(true);
		$response = curl_exec($conn);
		$end = microtime(true);
		
		// Checks if error exists
		$errorNumber = curl_errno($conn);
		
		$response = new Elastica_Response($response);
		
		if (defined('DEBUG') && DEBUG) { 
			$response->setQueryTime($end - $start);
			$response->setTransferInfo(curl_getinfo($conn));
		}
		
		if ($response->hasError()) {
			throw new Elastica_Exception_Response($response);
		}
		
		if ($errorNumber > 0) {
			throw new Elastica_Exception_Client($errorNumber, $request, $response);
		}
		
		return $response;
	}
}
