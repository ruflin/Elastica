<?php
/**
 * Single document stored in elastic search
 *
 * @category Xodoa
 * @package  Elastica
 * @author   Nicolas Ruflin <spam@ruflin.com>
 */
class Elastica_Document extends Elastica_Param
{
    /**
     * Document data
     *
     * @var array Document data
     */
    protected $_data = array();

    /**
     * Optype
     *
     * @var string Optype
     */
    protected $_optype = '';

    /**
     * Percolate
     *
     * @var string Percolate
     */
    protected $_percolate = '';

    /**
     * Routing
     *
     * @var string Routing
     */
    protected $_routing = null;

    /**
     * Creates a new document
     *
     * @param int|string $id OPTIONAL $id Id is create if empty
     * @param array  $data  OPTIONAL Data array
     * @param string $type  OPTIONAL Type name
     * @param string $index OPTIONAL Index name
     */
    public function __construct($id = '', array $data = array(), $type = '', $index = '')
    {
        $this->setId($id);
        $this->setData($data);
        $this->setType($type);
        $this->setIndex($index);
    }

    /**
     * Returns document id
     *
     * @return string|int Document id
     */
    public function getId()
    {
		return ($this->hasParam('_id'))?$this->getParam('_id'):null;
    }

    /**
     * Sets the id of the document.
     *
     * @param  string            $id
     * @return Elastica_Document
     */
    public function setId($id)
    {
		return $this->setParam('_id', $id);
    }

    /**
     * Adds the given key/value pair to the document
     *
     * @param  string            $key   Document entry key
     * @param  mixed             $value Document entry value
     * @return Elastica_Document
     */
    public function add($key, $value)
    {
        $this->_data[$key] = $value;

        return $this;
    }

    /**
     * Adds a file to the index
     *
     * To use this feature you have to call the following command in the
     * elasticsearch directory:
     * <code>
     * ./bin/plugin -install elasticsearch/elasticsearch-mapper-attachments/1.6.0
     * </code>
     * This installs the tika file analysis plugin. More infos about supported formats
     * can be found here: {@link http://tika.apache.org/0.7/formats.html}
     *
     * @param  string            $key      Key to add the file to
     * @param  string            $filepath Path to add the file
     * @param  string            $mimeType OPTIONAL Header mime type
     * @return Elastica_Document
     */
    public function addFile($key, $filepath, $mimeType = '')
    {
        $value = base64_encode(file_get_contents($filepath));

        if (!empty($mimeType)) {
            $value = array('_content_type' => $mimeType, '_name' => $filepath, 'content' => $value,);
        }

        $this->add($key, $value);

        return $this;
    }

    /**
     * Add file content
     *
     * @param  string            $key     Document key
     * @param  string            $content Raw file content
     * @return Elastica_Document
     */
    public function addFileContent($key, $content)
    {
        return $this->add($key, base64_encode($content));
    }

    /**
     * Adds a geopoint to the document
     *
     * Geohashes are not yet supported
     *
     * @param string $key       Field key
     * @param float  $latitude  Latitude value
     * @param float  $longitude Longitude value
     * @link http://www.elasticsearch.org/guide/reference/mapping/geo-point-type.html
     * @return Elastica_Document
     */
    public function addGeoPoint($key, $latitude, $longitude)
    {
        $value = array('lat' => $latitude, 'lon' => $longitude,);

        $this->add($key, $value);

        return $this;
    }

    /**
     * Overwrites the current document data with the given data
     *
     * @param  array             $data Data array
     * @return Elastica_Document
     */
    public function setData(array $data)
    {
        $this->_data = $data;

        return $this;
    }

    /**
     * Sets lifetime of document
     *
     * @param  string            $ttl
     * @return Elastica_Document
     */
    public function setTtl($ttl)
    {
        return $this->add('_ttl', $ttl);
    }

    /**
     * Returns the document data
     *
     * @return array Document data
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     * Sets the document type name
     *
     * @param  string            $type Type name
     * @return Elastica_Document Current object
     */
    public function setType($type)
    {
		return $this->setParam('_type', $type);
    }

    /**
     * Return document type name
     *
     * @return string                     Document type name
     * @throws Elastica_Exception_Invalid
     */
    public function getType()
    {
       return $this->getParam('_type');
    }

    /**
     * Sets the document index name
     *
     * @param  string            $index Index name
     * @return Elastica_Document Current object
     */
    public function setIndex($index)
    {
		return $this->setParam('_index', $index);
    }

    /**
     * Get the document index name
     *
     * @return string                     Index name
     * @throws Elastica_Exception_Invalid
     */
    public function getIndex()
    {
        return $this->getParam('_index');
    }

    /**
     * Sets the version of a document for use with optimistic concurrency control
     *
     * @param  int               $version Document version
     * @return Elastica_Document Current object
     * @link http://www.elasticsearch.org/blog/2011/02/08/versioning.html
     */
    public function setVersion($version)
    {
        return $this->setParam('_version', (int) $version);
    }

    /**
     * Returns document version
     *
     * @return string|int Document version
     */
    public function getVersion()
    {
		return $this->getParam('_version');
    }

    /**
     * Sets the version_type of a document
     * Default in ES is internal, but you can set to external to use custom versioning
     *
     * @param int $versionType Document version type
     * @return Elastica_Document Current object
     * @link http://www.elasticsearch.org/guide/reference/api/index_.html
     */
    public function setVersionType($versionType)
    {
        return $this->setParam('_version_type', $versionType);
    }

    /**
     * Returns document version type
     *
     * @return string|int Document version type
     */
    public function getVersionType()
    {
		return $this->getParam('_version_type');
    }

    /**
     * Sets parent document id
     *
     * @param  string|int        $parent Parent document id
     * @return Elastica_Document Current object
     * @link http://www.elasticsearch.org/guide/reference/mapping/parent-field.html
     */
    public function setParent($parent)
    {
        return $this->setParam('_parent', $parent);
    }

    /**
     * Returns the parent document id
     *
     * @return string|int Parent document id
     */
    public function getParent()
    {
		return $this->getParam('_parent');
    }

    /**
     * Set operation type
     *
     * @param  string            $optype Only accept create
     * @return Elastica_Document Current object
     */
    public function setOpType($optype)
    {
        $this->_optype = $optype;

        return $this;
    }

    /**
     * Get operation type
     */
    public function getOpType()
    {
        return $this->_optype;
    }

    /**
     * Set percolate query param
     *
     * @param  string            $value percolator filter
     * @return Elastica_Document
     */
    public function setPercolate($value = '*')
    {
        $this->_percolate = $value;

        return $this;
    }

    /**
     * Get percolate parameter
     *
     * @return string
     */
    public function getPercolate()
    {
        return $this->_percolate;
    }

    /**
     * Set routing query param
     *
     * @param  string            $value routing
     * @return Elastica_Document
     */
    public function setRouting($value)
    {
		return $this->setParam('_routing', $value);
    }

    /**
     * Get routing parameter
     *
     * @return string
     */
    public function getRouting()
    {
        return $this->getParam('_routing');
    }

    /**
     * Returns the document as an array
     * @return array
     */
    public function toArray()
    {
		$doc = $this->getParams();
        $doc['_source'] = $this->getData();

        return $doc;
    }
}
