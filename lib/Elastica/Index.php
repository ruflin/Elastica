<?php
/**
 * Elastica index object
 * 
 * Handles reads, deletes and configurations of an index
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class Elastica_Index
{

	/**
	 * @var string Index name
	 */
	protected $_indexName = '';
	
	/**
	 * @var Elastica_Client Client object
	 */
	protected $_client = null;

	/**
	 * Creates a new index object
	 *
	 * All the communication to and from an index goes of this object
	 *
	 * @param string $indexName Index name
	 */
	public function __construct(Elastica_Client $client, $indexName) {
		$this->_client = $client;
		
		if (!is_string($indexName)) {
			throw new Elastica_Exception('Indexname should be of type string');
		}
		$this->_indexName = $indexName;
	}
	
	/**
	 * Returns a type object for the current index with the given name
	 * 
	 * @param string $type Type name
	 * @return Elastica_Type Type object
	 */
	public function getType($type) {
		return new Elastica_Type($this, $type);
	}

	/**
	 * Returns the current status of the index
	 * 
	 * @link http://www.elasticsearch.com/docs/elasticsearch/rest_api/admin/indices/status/
	 * @return array Index status
	 */
	public function getStatus() {
		return $this->request('_status', Elastica_Request::GET);
	}
	
	/**
	 * Uses _bulk to send documents to the server
	 * 
	 * @link http://www.elasticsearch.com/docs/elasticsearch/rest_api/bulk/
	 * @param array $docs Array of Elastica_Document
	 */
	public function addDocuments(array $docs) {
		foreach ($docs as $doc) {
			$doc->setIndex($this->getIndexName());
		}
		
		return $this->getClient()->addDocuments($docs);
	}

	/**
	 * Deletes the index
	 * 
	 * @return <type>
	 */
	public function delete() {
		$response = $this->request('', Elastica_Request::DELETE);	
		
		return $response;
	}
	
	/**
	 * Optimizes search index
	 * 
	 * Detailed arguments can be found here in the link
	 * 
	 * @link http://www.elasticsearch.com/docs/elasticsearch/rest_api/admin/indices/optimize/
	 * @param array $args OPTIONAL Additional arguments
	 * @return array Server response
	 */
	public function optimize($args = array()) {
		// TODO: doesn't seem to work?
		$this->request('_optimize', Elastica_Request::POST, $args);
	}
	
	/**
	 * Refreshs the index
	 * 
	 * @link http://www.elasticsearch.com/docs/elasticsearch/rest_api/admin/indices/refresh/
	 * @return Elastica_Response Response object
	 */
	public function refresh() {
		return $this->request('_refresh', Elastica_Request::POST, array());
	}		

	/**
	 * Creates a new index with the given arguments
	 * 
	 * @link http://www.elasticsearch.com/docs/elasticsearch/rest_api/admin/indices/create_index/
	 * @param array $args OPTIONAL Arguments to use
	 * @param bool $recreate OPTIONAL Deletes index first if already exists (default = false)
	 * @return array Server response
	 */
	public function create(array $args = array(), $recreate = false) {
		if ($recreate) {
			try {
				$this->delete();
			} catch(Elastica_Exception_Response $e) {
				// Table can't be deleted, because doesn't exist			
			}
		}
		return $this->request('', Elastica_Request::PUT, $args);
	}
	
	public function search($query) {
		// TODO: implement
		$path = '_search';

		$response = $this->request($path, Elastica_Request::GET, $query);
		return new Elastica_ResultSet($response);
	}
		
	/**
	 * Returns the index name
	 * 
	 * @return string Index name
	 */
	public function getIndexName() {
		return $this->_indexName;
	}
	
	/**
	 * Returns index client
	 *
	 * @return Elastica_Client Index client object
	 */
	public function getClient() {
		return $this->_client;
	}
	
	/**
	 * Makes calls to the elasticsearch server based on this index
	 * 
	 * @param string $path Path to call
	 * @param string $method Rest method to use (GET, POST, DELETE, PUT)
	 * @param array $data OPTIONAL Arguments as array
	 * @return Elastica_Response Response object
	 */
	public function request($path, $method, $data = array()) {
		$path = $this->getIndexName() . '/' . $path;		
		return $this->getClient()->request($path, $method, $data);
	}
}
