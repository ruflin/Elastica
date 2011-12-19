<?php
/**
 * Elastica search object
 *
 * @category Xodoa
 * @package  Elastica
 * @author   Nicolas Ruflin <spam@ruflin.com>
 */
class Elastica_Search {
	protected $_indices = array();
	protected $_types = array();

	/**
	 * @var Elastica_Client
	 */
	protected $_client;

	/**
	 * Constructs search object
	 *
	 * @param Elastica_Client $client Client object
	 */
	public function __construct(Elastica_Client $client) {
		$this->_client = $client;
	}

	/**
	 * Adds a index to the list
	 *
	 * @param Elastica_Index|string $index Index object or string
	 * @return Elastica_Search Current object
	 */
	public function addIndex($index) {
		if ($index instanceof Elastica_Index) {
			$index = $index->getName();
		}

		if (!is_string($index)) {
			throw new Elastica_Exception_Invalid('Invalid param type');
		}

		$this->_indices[] = $index;

		return $this;
	}

	/**
	 * Add array of indices at once
	 *
	 * @param array $indices
	 * @return Elastica_Search
	 */
	public function addIndices(array $indices = array()) {
		foreach ($indices as $index) {
			$this->addIndex($index);
		}

		return $this;
	}

	/**
	 * Adds a type to the current search
	 *
	 * @param Elastica_Type|string $type Type name or object
	 * @return Elastica_Search Search object
	 * @throws Elastica_Exception_Invalid
	 */
	public function addType($type) {
		if ($type instanceof Elastica_Type) {
			$type = $type->getName();
		}

		if (!is_string($type)) {
			throw new Elastica_Exception_Invalid('Invalid type type');
		}

		$this->_types[] = $type;

		return $this;
	}

	/**
	 * Add array of types
	 *
	 * @param array $types
	 * @return Elastica_Search
	 */
	public function addTypes(array $types = array()) {
		foreach ($types as $type) {
			$this->addType($type);
		}

		return $this;
	}

	/**
	 * @return Elastica_Client Client object
	 */
	public function getClient() {
		return $this->_client;
	}

	/**
	 * @return string[] List of index names
	 */
	public function getIndices() {
		return $this->_indices;
	}

	/**
	 * @return string[] List of types
	 */
	public function getTypes() {
		return $this->_types;
	}

	/**
	 * Creates new search object
	 *
	 * @param Elastica_Searchable $searchObject
	 */
	public static function create(Elastica_Searchable $searchObject) {
		// Set index
		// set type
		// set client
	}

	/**
	 * Combines indices and types to the search request path
	 *
	 * @return string Search path
	 */
	public function getPath() {

		$indices = $this->getIndices();

		$path = '';

		if (empty($indices)) {
			$path .= '_all';
		} else {
			$path .= implode(',', $indices);
		}

		$types = $this->getTypes();

		if (!empty($types)) {
			$path .= '/' . implode(',', $types);
		}

		// Add full path based on indices and types -> could be all
		return $path . '/_search';
	}

	/**
	 * Search in the set indices, types
	 *
	 * @param mixed $query
	 * @param int   $limit OPTIONAL
	 * @return Elastica_ResultSet
	 */
	public function search($query, $limit = 0) {
		$query = Elastica_Query::create($query);
		if ($limit) {
			$query->setLimit($limit);
		}
		$path = $this->getPath();

		$response = $this->getClient()->request($path, Elastica_Request::GET, $query->toArray());
		return new Elastica_ResultSet($response);
	}
}