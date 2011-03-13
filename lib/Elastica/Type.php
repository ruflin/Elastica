<?php
/**
 * Elastica type object
 *
 * elasticsearch has for every types as a substructure. This object
 * represents a type inside a context
 * The hirarchie is as following: client -> index -> type -> document
 *
 * Search over different indices and types is not supported yet {@link http://www.elasticsearch.com/docs/elasticsearch/rest_api/search/indices_types/}
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class Elastica_Type
{

	/**
	 * @var Elastica_Index Index object
	 */
	protected $_index = null;

	/**
	 * @var string Object type
	 */
	protected $_type = '';

	/**
	 * Creates a new type object inside the given index
	 *
	 * @param Elastica_Index $index Index Object
	 * @param string $type Type name
	 */
	public function __construct(Elastica_Index $index, $type) {
		$this->_index = $index;
		$this->_type = $type;
	}

	/**
	 * Adds the given document to the search index
	 *
	 * @param Elastica_Document $doc Document with data
	 * @return Elastica_Response
	 */
	public function addDocument(Elastica_Document $doc) {
		$path = $doc->getId();
		return $this->request($path, Elastica_Request::PUT, $doc->getData());
	}

	/**
	 * Uses _bulk to send documents to the server
	 *
	 * @link http://www.elasticsearch.com/docs/elasticsearch/rest_api/bulk/
	 * @param array $docs Array of Elastica_Document
	 */
	public function addDocuments(array $docs) {

		foreach($docs as $doc) {
			$doc->setType($this->getType());
		}

		return $this->getIndex()->addDocuments($docs);
	}

	/**
	 * Returns the type name
	 *
	 * @return string Type
	 */
	public function getType() {
		return $this->_type;
	}

	/**
	 * Returns the number of items in this type
	 *
	 * @return int Number of items
	 */
	public function getCount() {
		$path = '_count';
		// TODO: test
		$response = $this->request($path, Elastica_Request::GET, array('matchAll' => array()))->getData();
		return (int) $response['count'];
	}


	/**
	 * Sets value type mapping for this type
	 *
	 * @param array $properties Property array with all mappings
	 */
	public function setMapping(array $properties, $source = true) {
		$path = '_mapping';

		$data = array($this->getType() => array('properties' => $properties, '_source' => array('enabled' => $source)));

		return $this->request($path, Elastica_Request::PUT, $data);
	}

	/**
	 * Returns current mapping for the given type
	 *
	 * @return array Current mapping
	 */
	public function getMapping() {
		$path = '_mapping';

		$response = $this->request($path, Elastica_Request::GET);
		return $response->getData();
	}

	/**
	 * Example code
	 *
	 * TODO: Improve sample code
	 * {
	 *	 "from" : 0,
	 *	 "size" : 10,
	 *	 "sort" : {
	 *		  "postDate" : {"reverse" : true},
	 *		  "user" : { },
	 *		  "_score" : { }
	 *	  },
	 *	  "query" : {
	 *		  "term" : { "user" : "kimchy" }
	 *	  }
	 * }
	 *
	 * @param array|Elastica_Query Array with all querie data inside or a Elastica_Query object
	 * @return Elastica_ResultSet ResultSet with all results inside
	 */
	public function search($query) {

		if ($query instanceof Elastica_Query) {
			$query = $query->toArray();
		} else if ($query instanceof Elastica_Query_Abstract) {
			// Converts query object
			$queryObject = new Elastica_Query($query);
			$query = $queryObject->toArray();
		} else if (is_string($query)) {
			// Assumes is string query
			$queryObject = new Elastica_Query(new Elastica_Query_QueryString($query));
			$query = $queryObject->toArray();
		} else {
			// TODO: Implement queries without
			throw new Elastica_Exception_NotImplemented();
		}

		$path = '_search';

		$response = $this->request($path, Elastica_Request::GET, $query);
		return new Elastica_ResultSet($response);
	}

	/**
	 * Returns index client
	 *
	 * @return Elastica_Index Index object
	 */
	public function getIndex() {
		return $this->_index;
	}

	/**
	 * Delete an entry by its unique identifier
	 *
	 * @link http://www.elasticsearch.org/guide/reference/api/delete.html
	 * @param int|string $id
	 */
	public function deleteById($id) {
		if (empty($id) || !trim($id)) {
			throw new InvalidArgumentException();
		}
		$response = $this->request($id, Elastica_Request::DELETE);
	}

	/**
	 * Deletes entries in the db based on a query
	 *
	 * @link http://www.elasticsearch.org/guide/reference/api/delete-by-query.html
	 * @param Elastica_Query $query
	 */
	public function deleteByQuery(Elastica_Query $query) {
		// TODO: To be implemented, can also be implemented on index and client level (see docs)
		throw new Elastica_Exception_NotImplemented();
	}

	/**
	 * More like this query based on the given object
	 *
	 * The id in the given object has to be set
	 *
	 * @link http://www.elasticsearch.com/docs/elasticsearch/rest_api/more_like_this/
	 * @param EalsticSearch_Document $doc Document to query for similar objects
	 * @param array $args OPTIONAL Additional arguments for the query
	 */
	public function moreLikeThis(Elastica_Document $doc, $args = array()) {
		// TODO: Not tested yet
		$path = $doc->getId() . '/_mlt';
		return $this->request($path, Elastica_Request::GET, $args);
	}

	/**
	 * Makes calls to the elasticsearch server based on this type
	 *
	 * @param string $path Path to call
	 * @param string $method Rest method to use (GET, POST, DELETE, PUT)
	 * @param array $data OPTIONAL Arguments as array
	 * @return Elastica_Response Response object
	 */
	public function request($path, $method, $data = array()) {
		$path = $this->getType() . '/' . $path;
		return $this->getIndex()->request($path, $method, $data);
	}
}
