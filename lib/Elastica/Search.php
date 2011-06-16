<?php
/**
 * Elastica search object
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class Elastica_Search
{
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

	public function getTypes() {
		return $this->_types;
	}

	public static function create(Elastica_Searchable $searchObject) {
		// Set index
		// set type
		// set client
	}


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

	public function search($query) {
		$query = Elastica_Query::create($query);
		$path = $this->getPath();

		$response = $this->getClient()->request($path, Elastica_Request::GET, $query->toArray());
		return new Elastica_ResultSet($response);
	}
}