<?php
/**
 * Single document stored in elastic search
 *
 * @category Xodoa
 * @package  Elastica
 * @author   Nicolas Ruflin <spam@ruflin.com>
 */
class Elastica_Document {

	/**
	 * @var string|int	Document id
	 */
	protected $_id = '';

	/**
	 * @var array Document data
	 */
	protected $_data = array();

	protected $_type = '';
	protected $_index = '';

	protected $_version = '';

	/**
	 * @var string|int Parent document id
	 */
	protected $_parent = '';

	/**
	 * @var string Optype
	 */
	protected $_optype = '';

	/**
	 * @var string Percolate
	 */
	protected $_percolate = '';

	/**
	 * Creates a new document
	 *
	 * @param int    $id    OPTIONAL $id Id is create if empty
	 * @param array  $data  OPTIONAL Data array
	 * @param string $type  OPTIONAL Type name
	 * @param string $index OPTIONAL Index name
	 */
	public function __construct($id = '', array $data = array(), $type = '', $index = '') {
		$this->_id = $id;
		$this->setData($data);
		$this->setType($type);
		$this->setIndex($index);
	}

	/**
	 * Returns document id
	 *
	 * @return string|int Document id
	 */
	public function getId() {
		return $this->_id;
	}

	/**
	 * Adds the given key/value pair to the document
	 *
	 * @param string $key   Document entry key
	 * @param mixed  $value Document entry value
	 * @return Elastica_Document
	 */
	public function add($key, $value) {
		$this->_data[$key] = $value;
		return $this;
	}

	/**
	 * Adds a file to the index
	 *
	 * To use this feature you have to call the following command in the
	 * elasticsearch directory:
	 * <code>
	 * ./bin/plugin install mapper-attachments
	 * </code>
	 * This installs the tika file analysis plugin. More infos about supported formats
	 * can be found here: {@link http://tika.apache.org/0.7/formats.html}
	 *
	 * @param string $key      Key to add the file to
	 * @param string $filepath Path to add the file
	 * @param string $mimeType OPTIONAL Header mime type
	 * @return Elastica_Document
	 */
	public function addFile($key, $filepath, $mimeType = '') {
		$value = base64_encode(file_get_contents($filepath));

		if (!empty($mimeType)) {
			$value = array('_content_type' => $mimeType, '_name' => $filepath, 'content' => $value,);
		}

		$this->add($key, $value);
		return $this;
	}

	/**
	 * @param string $key     Document key
	 * @param string $content Raw file content
	 * @return Elastica_Document
	 */
	public function addFileContent($key, $content) {
		return $this->add($key, base64_encode($content));
	}

	/**
	 * Adds a geopoint to the document
	 *
	 * Geohashes re not yet supported
	 *
	 * @param string $key       Field key
	 * @param float  $latitude  Latitud value
	 * @param float  $longitude Longitude value
	 * @link http://www.elasticsearch.com/docs/elasticsearch/mapping/geo_point/
	 * @return Elastica_Document
	 */
	public function addGeoPoint($key, $latitude, $longitude) {

		$value = array('lat' => $latitude, 'lon' => $longitude,);

		$this->add($key, $value);
		return $this;
	}

	/**
	 * Overwrites the curent document data with the given data
	 *
	 * @param array $data Data array
	 * @return Elastica_Document
	 */
	public function setData(array $data) {
		$this->_data = $data;
		return $this;
	}

	/**
	 * Sets lifetime of document
	 *
	 * @param string $ttl
	 * @return Elastica_Document
	 */
	public function setTTL($ttl) {
		return $this->add('_ttl', $ttl);
	}

	/**
	 * Returns the document data
	 *
	 * @return array Document data
	 */
	public function getData() {
		return $this->_data;
	}

	/**
	 * Sets the document type name
	 *
	 * @param string $type Type name
	 * @return Elastica_Document Current object
	 */
	public function setType($type) {
		$this->_type = $type;
		return $this;
	}

	/**
	 * @return string Document type name
	 * @throws Elastica_Exception_Invalid
	 */
	public function getType() {
		$type = $this->_type;

		if (empty($type)) {
			throw new Elastica_Exception_Invalid('Type not set');
		}
		return $type;
	}

	/**
	 * Sets the document index name
	 *
	 * @param string $index Index name
	 * @return Elastica_Document Current object
	 */
	public function setIndex($index) {
		$this->_index = $index;
		return $this;
	}

	/**
	 * @return string Index name
	 * @throws Elastica_Exception_Invalid
	 */
	public function getIndex() {
		$index = $this->_index;

		if (empty($index)) {
			throw new Elastica_Exception_Invalid('Index not set');
		}
		return $index;
	}

	/**
	 * Sets the version of a document for use with optimistic concurrency control
	 *
	 * @param int $version Document version
	 * @return Elastica_Document Current object
	 * @link http://www.elasticsearch.org/blog/2011/02/08/versioning.html
	 */
	public function setVersion($version) {
		if ($version !== '') {
			$this->_version = (int) $version;
		}
		return $this;
	}

	/**
	 * Returns document version
	 *
	 * @return string|int Document version
	 */
	public function getVersion() {
		return $this->_version;
	}

	/**
	 * Sets parent document id
	 *
	 * @param string|int $parent Parent document id
	 * @return Elastica_Document Current object
	 * @link http://www.elasticsearch.org/guide/reference/mapping/parent-field.html
	 */
	public function setParent($parent) {
		$this->_parent = $parent;
		return $this;
	}

	/**
	 * Returns the parent document id
	 *
	 * @return string|int Parent document id
	 */
	public function getParent() {
		return $this->_parent;
	}

	/**
	 * Set operation type
	 *
	 * @param string $optype Only accept create
	 * @return Elastica_Document Current object
	 */
	public function setOpType($optype) {
		$this->_optype = $optype;
		return $this;
	}

	/**
	 * Get operation type
	 */
	public function getOpType() {
		return $this->_optype;
	}

	/**
	 * Set percolate query param
	 *
	 * @param string $value percolator filter
	 * @return Elastica_Document
	 */
	public function setPercolate($value = '*') {
		$this->_percolate = $value;
		return $this;
	}

	/**
	 * Get percolate parameter
	 *
	 * @return string
	 */
	public function getPercolate() {
		return $this->_percolate;
	}

	/**
	 * Returns the document as an array
	 * @return array
	 */
	public function toArray() {
		$index = array('_index' => $this->getIndex(), '_type' => $this->getType(), '_id' => $this->getId());

		$version = $this->getVersion();
		if (!empty($version)) {
			$index['_version'] = $version;
		}

		$parent = $this->getParent();
		if (!empty($parent)) {
			$index['_parent'] = $parent;
		}

		$params[] = $action;
		$params[] = $doc->getData();
	}
}
