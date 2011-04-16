<?php
/**
 * Single document stored in elastic search
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
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
	 * Creates a new document
	 *
	 * @param int OPTIONAL $id Id is create if empty
	 * @param array $data OPTIONAL Data array
	 * @param string $type OPTIONAL Type name
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
	 * @param string $key Document entry key
	 * @param mixed $value Document entry value
	 */
	public function add($key, $value) {
		$this->_data[$key] = $value;
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
	 * @param string $key Key to add the file to
	 * @param string $filepath Path to add the file
	 * @param string $mimeType OPTIONAL Header mime type
	 */
	public function addFile($key, $filepath, $mimeType = '') {
		$value = base64_encode(file_get_contents($filepath));

		if (!empty($mimeType)) {
			$value = array(
				'_content_type' => $mimeType,
				'_name' => $filepath,
				'content' => $value,
			);
		}

		$this->add($key, $value);
	}

	/**
	 * Adds a geopoint to the document
	 *
	 * Geohashes re not yet supported
	 *
	 * @link http://www.elasticsearch.com/docs/elasticsearch/mapping/geo_point/
	 * @param string $key Field key
	 * @param float $latitude Latitud value
	 * @param float $longitude Longitude value
	 */
	public function addGeoPoint($key, $latitude, $longitude) {

		$value = array(
			'lat' => $latitude,
			'lon' => $longitude,
		);

		$this->add($key, $value);
	}

	/**
	 * Overwrites the curent document data with the given data
	 *
	 * @param array $data Data array
	 */
	public function setData(array $data) {
		$this->_data = $data;
	}

	/**
	 * Returns the document data
	 *
	 * @return array Document data
	 */
	public function getData() {
		return $this->_data;
	}

	public function setType($type) {
		$this->_type = $type;
	}

	public function getType() {
		$type = $this->_type;

		if (empty($type)) {
			throw new Elastica_Exception_Invalid('Type not set');
		}
		return $type;
	}

	public function setIndex($index) {
		$this->_index = $index;
	}

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
	 * @link http://www.elasticsearch.org/blog/2011/02/08/versioning.html
	 * @param int $version Document version
	 */
	public function setVersion($version) {
		if($version !== '') {
			$this->_version = (int) $version;
		}
	}

	/**
	 * Returns document version
	 *
	 * @return string|int Document version
	 */
	public function getVersion() {
		return $this->_version;
	}
}
