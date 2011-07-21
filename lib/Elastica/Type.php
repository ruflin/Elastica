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
class Elastica_Type implements Elastica_Searchable
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

		$query = array();
		if($doc->getVersion() > 0) {
			$query['version'] = $doc->getVersion();
		}
		if($doc->getParent()) {
			$query['parent'] = $doc->getParent();
		}
		if (count($query) > 0) {
			$path .= '?' . http_build_query($query);
		}

		return $this->request($path, Elastica_Request::PUT, $doc->getData());
	}

	/**
	 * Uses _bulk to send documents to the server
	 *
	 * @param array $docs Array of Elastica_Document
	 * @link http://www.elasticsearch.com/docs/elasticsearch/rest_api/bulk/
	 */
	public function addDocuments(array $docs) {

		foreach($docs as $doc) {
			$doc->setType($this->getType());
		}

		return $this->getIndex()->addDocuments($docs);
	}

	/**
	 * Get the document from search index
	 *
	 * @param string $id Document id
	 * @return Elastica_Document
	 */
	public function getDocument($id) {
		$path = $id;

		$result = $this->request($path, Elastica_Request::GET)->getData();

		$document = new Elastica_Document($id, $result['_source'], $this->getType(),  $this->getIndex());
		$document->setVersion($result['_version']);
		return $document;
	}

	/**
	 * @return string Type name
	 */
	public function getName() {
		return $this->getType();
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
	 * Sets value type mapping for this type
	 *
	 * @param Elastica_Type_Mapping|array $mapping Elastica_Type_Mapping object or property array with all mappings
	 * @param bool $source OPTIONAL If source should be stored or not (default = true)
	 */
	public function setMapping($mapping) {

		$mapping = Elastica_Type_Mapping::create($mapping);
		$mapping->setType($this);
		return $mapping->send();
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
	 * @param string|array|Elastica_Query $query Array with all query data inside or a Elastica_Query object
	 * @return Elastica_ResultSet ResultSet with all results inside
	 * @see Elastica_Searchable::search
	 */
	public function search($query) {
		$query = Elastica_Query::create($query);
		$path = '_search';

		$response = $this->request($path, Elastica_Request::GET, $query->toArray());
		return new Elastica_ResultSet($response);
	}

	/**
	 * @param string|array|Elastica_Query $query Array with all query data inside or a Elastica_Query object
	 * @return int number of documents matching the query
	 * @see Elastica_Searchable::count
	 */
	public function count($query = '') {
		$query = Elastica_Query::create($query);
		$path = '_count';

		$data = $this->request($path, Elastica_Request::GET, $query->getQuery())->getData();
		return (int) $data['count'];
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
	 * Deletes an entry by its unique identifier
	 *
	 * @param int|string $id Document id
	 * @return Elastica_Response Response object
	 * @link http://www.elasticsearch.org/guide/reference/api/delete.html
	 */
	public function deleteById($id) {
		if (empty($id) || !trim($id)) {
			throw new InvalidArgumentException();
		}
		return $this->request($id, Elastica_Request::DELETE);
	}

	/**
	 * Deletes entries in the db based on a query
	 *
	 * @param Elastica_Query $query Query object
	 * @link http://www.elasticsearch.org/guide/reference/api/delete-by-query.html
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
	 * @param EalsticSearch_Document $doc Document to query for similar objects
	 * @param array $args OPTIONAL Additional arguments for the query
	 * @link http://www.elasticsearch.com/docs/elasticsearch/rest_api/more_like_this/
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
