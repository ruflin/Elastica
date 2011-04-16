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
class Elastica_Index implements Elastica_Searchable
{
	/**
	 * @var string Index name
	 */
	protected $_name = '';

	/**
	 * @var Elastica_Client Client object
	 */
	protected $_client = null;

	/**
	 * Creates a new index object
	 *
	 * All the communication to and from an index goes of this object
	 *
	 * @param string $name Index name
	 */
	public function __construct(Elastica_Client $client, $name) {
		$this->_client = $client;

		if (!is_string($name)) {
			throw new Elastica_Exception_Invalid('Index name should be of type string');
		}
		$this->_name = $name;
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
		return new Elastica_Index_Status($this);
	}

	/**
	 * Returns the index settings object
	 *
	 * @return Elastica_Index_Settings Settings object
	 */
	public function getSettings() {
		return new Elastica_Index_Settings($this);
	}

	/**
	 * Uses _bulk to send documents to the server
	 *
	 * @link http://www.elasticsearch.com/docs/elasticsearch/rest_api/bulk/
	 * @param array $docs Array of Elastica_Document
	 */
	public function addDocuments(array $docs) {
		foreach ($docs as $doc) {
			$doc->setIndex($this->getName());
		}

		return $this->getClient()->addDocuments($docs);
	}

	/**
	 * Deletes the index
	 *
	 * @return Elastica_Response Response object
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

	/**
	 * {@inheritDoc}
	 */
	public function search($query) {
		$query = Elastica_Query::create($query);
		$path = '_search';

		$response = $this->request($path, Elastica_Request::GET, $query->toArray());
		return new Elastica_ResultSet($response);
	}

	/**
	 * {@inheritDoc}
	 */
	public function count($query) {
		$query = Elastica_Query::create($query);
		$path = '_count';

		$data = $this->request($path, Elastica_Request::GET, $query->getQuery())->getData();
		return (int) $data['count'];
	}

	/**
	 * Opens an index
	 *
	 * @link http://www.elasticsearch.org/guide/reference/api/admin-indices-open-close.html
	 * @return Elastica_Response Response object
	 */
	public function open() {
		$this->request('_open', Elastica_Request::POST);
	}

	/**
	 * Closes the index
	 *
	 * @link http://www.elasticsearch.org/guide/reference/api/admin-indices-open-close.html
	 * @return Elastica_Response Response object
	 */
	public function close() {
		return $this->request('_close', Elastica_Request::POST);
	}

	/**
	 * Returns the index name
	 *
	 * @return string Index name
	 */
	public function getName() {
		return $this->_name;
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
	 * Adds an alias to the current index
	 *
	 * @link http://www.elasticsearch.org/guide/reference/api/admin-indices-aliases.html
	 * @param string $name Alias name
	 * @param bool $replace OPTIONAL If set, an existing alias will be replaced
	 * @return Elastica_Response Response
	 */
	public function addAlias($name, $replace = false) {
		$path = '_aliases';

		if ($replace) {
			$status = new Elastica_Status($this->getClient());

			foreach ($status->getIndicesWithAlias($name) as $index) {
				$index->removeAlias($name);
			}
		}

		$data = array(
			'actions' => array(
				array(
					'add' => array(
						'index' => $this->getName(),
						'alias' => $name
					)
				)
			)
		);

		return $this->getClient()->request($path, Elastica_Request::POST, $data);
	}

	/**
	 * Removes an alias pointing to the current index
	 *
	 * @link http://www.elasticsearch.org/guide/reference/api/admin-indices-aliases.html
	 * @param string $name Alias name
	 * @return Elastica_Response Response
	 */
	public function removeAlias($name) {
		$path = '_aliases';

		$data = array(
			'actions' => array(
				array(
					'remove' => array(
						'index' => $this->getName(),
						'alias' => $name
					)
				)
			)
		);

		return $this->getClient()->request($path, Elastica_Request::POST, $data);
	}

	/**
	 * Can be used to change settings during runtime. One example is to use
	 * if for bulk updating {@link http://www.elasticsearch.org/blog/2011/03/23/update-settings.html}
	 *
	 * @link http://www.elasticsearch.org/guide/reference/api/admin-indices-update-settings.html
	 * @param array $data Data array
	 * @return Elastica_Response Response object
	 */
	public function setSettings(array $data) {
		return $this->request('_settings', Elastica_Request::PUT, $data);
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
		$path = $this->getName() . '/' . $path;
		return $this->getClient()->request($path, $method, $data);
	}
}
