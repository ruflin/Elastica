<?php

namespace Elastica;

use Elastica\Bulk\Action;
use Elastica\Exception\InvalidException;
use Elastica\Exception\NotImplementedException;
use Elastica\Filter\Bool;

/**
 * Single document stored in elastic search
 *
 * @category Xodoa
 * @package  Elastica
 * @author   Nicolas Ruflin <spam@ruflin.com>
 */
class Document extends AbstractUpdateAction
{
    const OP_TYPE_CREATE = Action::OP_TYPE_CREATE;

    /**
     * Document data
     *
     * @var array Document data
     */
    protected $_data = array();

    /**
     * Whether to use this document to upsert if the document does not exist.
     *
     * @var boolean
     */
    protected $_docAsUpsert = false;

    /**
     * @var boolean
     */
    protected $_autoPopulate = false;

    /**
     * Creates a new document
     *
     * @param int|string   $id    OPTIONAL $id Id is create if empty
     * @param array|string $data  OPTIONAL Data array
     * @param string       $type  OPTIONAL Type name
     * @param string       $index OPTIONAL Index name
     */
    public function __construct($id = '', $data = array(), $type = '', $index = '')
    {
        $this->setId($id);
        $this->setData($data);
        $this->setType($type);
        $this->setIndex($index);
    }

    /**
     * @param  string $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * @param string $key
     * @param mixed  $value
     */
    public function __set($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * @param  string $key
     * @return bool
     */
    public function __isset($key)
    {
        return $this->has($key) && null !== $this->get($key);
    }

    /**
     * @param string $key
     */
    public function __unset($key)
    {
        $this->remove($key);
    }

    /**
     * @param  string                               $key
     * @return mixed
     * @throws \Elastica\Exception\InvalidException
     */
    public function get($key)
    {
        if (!$this->has($key)) {
            throw new InvalidException("Field {$key} does not exist");
        }

        return $this->_data[$key];
    }

    /**
     * @param  string                               $key
     * @param  mixed                                $value
     * @throws \Elastica\Exception\InvalidException
     * @return \Elastica\Document
     */
    public function set($key, $value)
    {
        if (!is_array($this->_data)) {
            throw new InvalidException('Document data is serialized data. Data creation is forbidden.');
        }
        $this->_data[$key] = $value;

        return $this;
    }

    /**
     * @param  string $key
     * @return bool
     */
    public function has($key)
    {
        return is_array($this->_data) && array_key_exists($key, $this->_data);
    }

    /**
     * @param  string                               $key
     * @throws \Elastica\Exception\InvalidException
     * @return \Elastica\Document
     */
    public function remove($key)
    {
        if (!$this->has($key)) {
            throw new InvalidException("Field {$key} does not exist");
        }
        unset($this->_data[$key]);

        return $this;
    }

    /**
     * Adds the given key/value pair to the document
     *
     * @deprecated
     * @param  string             $key   Document entry key
     * @param  mixed              $value Document entry value
     * @return \Elastica\Document
     */
    public function add($key, $value)
    {
        return $this->set($key, $value);
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
     * @param  string             $key      Key to add the file to
     * @param  string             $filepath Path to add the file
     * @param  string             $mimeType OPTIONAL Header mime type
     * @return \Elastica\Document
     */
    public function addFile($key, $filepath, $mimeType = '')
    {
        $value = base64_encode(file_get_contents($filepath));

        if (!empty($mimeType)) {
            $value = array('_content_type' => $mimeType, '_name' => $filepath, '_content' => $value);
        }

        $this->set($key, $value);

        return $this;
    }

    /**
     * Add file content
     *
     * @param  string             $key     Document key
     * @param  string             $content Raw file content
     * @return \Elastica\Document
     */
    public function addFileContent($key, $content)
    {
        return $this->set($key, base64_encode($content));
    }

    /**
     * Adds a geopoint to the document
     *
     * Geohashes are not yet supported
     *
     * @param  string             $key       Field key
     * @param  float              $latitude  Latitude value
     * @param  float              $longitude Longitude value
     * @link http://www.elasticsearch.org/guide/reference/mapping/geo-point-type.html
     * @return \Elastica\Document
     */
    public function addGeoPoint($key, $latitude, $longitude)
    {
        $value = array('lat' => $latitude, 'lon' => $longitude);

        $this->set($key, $value);

        return $this;
    }

    /**
     * Overwrites the current document data with the given data
     *
     * @param  array|string       $data Data array
     * @return \Elastica\Document
     */
    public function setData($data)
    {
        $this->_data = $data;

        return $this;
    }

    /**
     * Returns the document data
     *
     * @return array|string Document data
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     * @param  \Elastica\Script        $data
     * @throws NotImplementedException
     * @deprecated
     */
    public function setScript($data)
    {
        throw new NotImplementedException("setScript() is no longer available as of 0.90.2. See http://elastica.io/migration/0.90.2/upsert.html to migrate");
    }

    /**
     * @throws NotImplementedException
     * @deprecated
     */
    public function getScript()
    {
        throw new NotImplementedException("getScript() is no longer available as of 0.90.2. See http://elastica.io/migration/0.90.2/upsert.html to migrate");
    }

    /**
     * @throws NotImplementedException
     * @deprecated
     */
    public function hasScript()
    {
        throw new NotImplementedException("hasScript() is no longer available as of 0.90.2. See http://elastica.io/migration/0.90.2/upsert.html to migrate");
    }

    /**
     * @param  bool               $value
     * @return \Elastica\Document
     */
    public function setDocAsUpsert($value)
    {
        $this->_docAsUpsert = (bool) $value;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getDocAsUpsert()
    {
        return $this->_docAsUpsert;
    }

    /**
     * @param  bool  $autoPopulate
     * @return $this
     */
    public function setAutoPopulate($autoPopulate = true)
    {
        $this->_autoPopulate = (bool) $autoPopulate;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAutoPopulate()
    {
        return $this->_autoPopulate;
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

    /**
     * @param  array|\Elastica\Document             $data
     * @throws \Elastica\Exception\InvalidException
     * @return \Elastica\Document
     */
    public static function create($data)
    {
        if ($data instanceof self) {
            return $data;
        } elseif (is_array($data)) {
            return new self('', $data);
        } else {
            throw new InvalidException('Failed to create document. Invalid data passed.');
        }
    }
}
