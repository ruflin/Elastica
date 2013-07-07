<?php

namespace Elastica;

use Elastica\Exception\InvalidException;
use Elastica\Bulk\Action;
use Elastica\Filter\Bool;
use Elastica\Exception\NotImplementedException;

/**
 * Single document stored in elastic search
 *
 * @category Xodoa
 * @package  Elastica
 * @author   Nicolas Ruflin <spam@ruflin.com>
 */
class Document extends Param
{
    const OP_TYPE_CREATE = Action::OP_TYPE_CREATE;

    /**
     * Document data
     *
     * @var array Document data
     */
    protected $_data = array();

    /**
     * @var \Elastica\Document
     */
    protected $_upsert;

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
     * @param int|string $id    OPTIONAL $id Id is create if empty
     * @param array|string  $data  OPTIONAL Data array
     * @param string     $type  OPTIONAL Type name
     * @param string     $index OPTIONAL Index name
     */
    public function __construct($id = '', $data = array(), $type = '', $index = '')
    {
        $this->setId($id);
        $this->setData($data);
        $this->setType($type);
        $this->setIndex($index);
    }

    /**
     * Sets the id of the document.
     *
     * @param  string            $id
     * @return \Elastica\Document
     */
    public function setId($id)
    {
        return $this->setParam('_id', $id);
    }

    /**
     * Returns document id
     *
     * @return string|int Document id
     */
    public function getId()
    {
        return ($this->hasParam('_id')) ? $this->getParam('_id') : null;
    }

    /**
     * @return bool
     */
    public function hasId()
    {
        return '' !== (string) $this->getId();
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function __set($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * @param string $key
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
     * @param string $key
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
     * @param string $key
     * @param mixed $value
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
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        return is_array($this->_data) && array_key_exists($key, $this->_data);
    }

    /**
     * @param string $key
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
     * @param  string            $key   Document entry key
     * @param  mixed             $value Document entry value
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
     * @param  string            $key      Key to add the file to
     * @param  string            $filepath Path to add the file
     * @param  string            $mimeType OPTIONAL Header mime type
     * @return \Elastica\Document
     */
    public function addFile($key, $filepath, $mimeType = '')
    {
        $value = base64_encode(file_get_contents($filepath));

        if (!empty($mimeType)) {
            $value = array('_content_type' => $mimeType, '_name' => $filepath, 'content' => $value,);
        }

        $this->set($key, $value);

        return $this;
    }

    /**
     * Add file content
     *
     * @param  string            $key     Document key
     * @param  string            $content Raw file content
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
     * @param string $key       Field key
     * @param float  $latitude  Latitude value
     * @param float  $longitude Longitude value
     * @link http://www.elasticsearch.org/guide/reference/mapping/geo-point-type.html
     * @return \Elastica\Document
     */
    public function addGeoPoint($key, $latitude, $longitude)
    {
        $value = array('lat' => $latitude, 'lon' => $longitude,);

        $this->set($key, $value);

        return $this;
    }

    /**
     * Overwrites the current document data with the given data
     *
     * @param  array|string             $data Data array
     * @return \Elastica\Document
     */
    public function setData($data)
    {
        $this->_data = $data;

        return $this;
    }

    /**
     * Sets lifetime of document
     *
     * @param  string            $ttl
     * @return \Elastica\Document
     */
    public function setTtl($ttl)
    {
        return $this->setParam('_ttl', $ttl);
    }

    /**
     * @return string
     */
    public function getTtl()
    {
        return $this->getParam('_ttl');
    }

    /**
     * @return bool
     */
    public function hasTtl()
    {
        return $this->hasParam('_ttl');
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
     * Sets the document type name
     *
     * @param  string            $type Type name
     * @return \Elastica\Document Current object
     */
    public function setType($type)
    {
        if ($type instanceof Type) {
            $this->setIndex($type->getIndex());
            $type = $type->getName();
        }
        return $this->setParam('_type', $type);
    }

    /**
     * Return document type name
     *
     * @return string                              Document type name
     * @throws \Elastica\Exception\InvalidException
     */
    public function getType()
    {
        return $this->getParam('_type');
    }

    /**
     * Sets the document index name
     *
     * @param  string            $index Index name
     * @return \Elastica\Document Current object
     */
    public function setIndex($index)
    {
        if ($index instanceof Index) {
            $index = $index->getName();
        }
        return $this->setParam('_index', $index);
    }

    /**
     * Get the document index name
     *
     * @return string                              Index name
     * @throws \Elastica\Exception\InvalidException
     */
    public function getIndex()
    {
        return $this->getParam('_index');
    }

    /**
     * Sets the version of a document for use with optimistic concurrency control
     *
     * @param  int               $version Document version
     * @return \Elastica\Document Current object
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
     * @return bool
     */
    public function hasVersion()
    {
        return $this->hasParam('_version');
    }

    /**
     * Sets the version_type of a document
     * Default in ES is internal, but you can set to external to use custom versioning
     *
     * @param  int               $versionType Document version type
     * @return \Elastica\Document Current object
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
     * @return bool
     */
    public function hasVersionType()
    {
        return $this->hasParam('_version_type');
    }

    /**
     * Sets parent document id
     *
     * @param  string|int        $parent Parent document id
     * @return \Elastica\Document Current object
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
     * @return bool
     */
    public function hasParent()
    {
        return $this->hasParam('_parent');
    }

    /**
     * Set operation type
     *
     * @param  string            $opType Only accept create
     * @return \Elastica\Document Current object
     */
    public function setOpType($opType)
    {
        return $this->setParam('_op_type', $opType);
    }

    /**
     * Get operation type
     * @return string
     */
    public function getOpType()
    {
        return $this->getParam('_op_type');
    }

    /**
     * @return bool
     */
    public function hasOpType()
    {
        return $this->hasParam('_op_type');
    }

    /**
     * Set percolate query param
     *
     * @param  string            $value percolator filter
     * @return \Elastica\Document
     */
    public function setPercolate($value = '*')
    {
        return $this->setParam('_percolate', $value);
    }

    /**
     * Get percolate parameter
     *
     * @return string
     */
    public function getPercolate()
    {
        return $this->getParam('_percolate');
    }

    /**
     * @return bool
     */
    public function hasPercolate()
    {
        return $this->hasParam('_percolate');
    }

    /**
     * Set routing query param
     *
     * @param  string            $value routing
     * @return \Elastica\Document
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
     * @return bool
     */
    public function hasRouting()
    {
        return $this->hasParam('_routing');
    }

    /**
     * @param array|string $fields
     * @return \Elastica\Document
     */
    public function setFields($fields)
    {
        if (is_array($fields)) {
            $fields = implode(',', $fields);
        }
        return $this->setParam('_fields', (string) $fields);
    }

    /**
     * @return \Elastica\Document
     */
    public function setFieldsSource()
    {
        return $this->setFields('_source');
    }

    /**
     * @return string
     */
    public function getFields()
    {
        return $this->getParam('_fields');
    }

    /**
     * @return bool
     */
    public function hasFields()
    {
        return $this->hasParam('_fields');
    }

    /**
     * @param int $num
     * @return \Elastica\Document
     */
    public function setRetryOnConflict($num)
    {
        return $this->setParam('_retry_on_conflict', (int) $num);
    }

    /**
     * @return int
     */
    public function getRetryOnConflict()
    {
        return $this->getParam('_retry_on_conflict');
    }

    /**
     * @return bool
     */
    public function hasRetryOnConflict()
    {
        return $this->hasParam('_retry_on_conflict');
    }

    /**
     * @param string $timestamp
     * @return \Elastica\Document
     */
    public function setTimestamp($timestamp)
    {
        return $this->setParam('_timestamp', $timestamp);
    }

    /**
     * @return int
     */
    public function getTimestamp()
    {
        return $this->getParam('_timestamp');
    }

    /**
     * @return bool
     */
    public function hasTimestamp()
    {
        return $this->hasParam('_timestamp');
    }

    /**
     * @param bool $refresh
     * @return \Elastica\Document
     */
    public function setRefresh($refresh = true)
    {
        return $this->setParam('_refresh', (bool) $refresh);
    }

    /**
     * @return bool
     */
    public function getRefresh()
    {
        return $this->getParam('_refresh');
    }

    /**
     * @return bool
     */
    public function hasRefresh()
    {
        return $this->hasParam('_refresh');
    }

    /**
     * @param string $timeout
     * @return \Elastica\Document
     */
    public function setTimeout($timeout)
    {
        return $this->setParam('_timeout', $timeout);
    }

    /**
     * @return bool
     */
    public function getTimeout()
    {
        return $this->getParam('_timeout');
    }

    /**
     * @return string
     */
    public function hasTimeout()
    {
        return $this->hasParam('_timeout');
    }

    /**
     * @param string $timeout
     * @return \Elastica\Document
     */
    public function setConsistency($timeout)
    {
        return $this->setParam('_consistency', $timeout);
    }

    /**
     * @return string
     */
    public function getConsistency()
    {
        return $this->getParam('_consistency');
    }

    /**
     * @return string
     */
    public function hasConsistency()
    {
        return $this->hasParam('_consistency');
    }

    /**
     * @param string $timeout
     * @return \Elastica\Document
     */
    public function setReplication($timeout)
    {
        return $this->setParam('_replication', $timeout);
    }

    /**
     * @return string
     */
    public function getReplication()
    {
        return $this->getParam('_replication');
    }

    /**
     * @return bool
     */
    public function hasReplication()
    {
        return $this->hasParam('_replication');
    }
    
    /**
     * @param \Elastica\Script $data
     * @throws NotImplementedException
     * @deprecated
     */
    public function setScript($data)
    {
       throw new NotImplementedException("setScript() is no longer avaliable as of 0.90.2. See http://elastica.io/migration/0.90.2/upsert.html to migrate");
    }

    /**
     * @param \Elastica\Document|array $data
     * @return \Elastica\Document
     */
    public function setUpsert($data)
    {
        $document = Document::create($data);
        $this->_upsert = $document;

        return $this;
    }
    
    /**
     * @throws NotImplementedException
     * @deprecated
     */
    public function getScript()
    {
        throw new NotImplementedException("getScript() is no longer avaliable as of 0.90.2. See http://elastica.io/migration/0.90.2/upsert.html to migrate");
    }

    /**
     * @return \Elastica\Document
     */
    public function getUpsert()
    {
        return $this->_upsert;
    }
    
    /**
     * @throws NotImplementedException
     * @deprecated
     */
    public function hasScript()
    {
        throw new NotImplementedException("hasScript() is no longer avaliable as of 0.90.2. See http://elastica.io/migration/0.90.2/upsert.html to migrate");
    }

    /**
     * @return bool
     */
    public function hasUpsert()
    {
        return null !== $this->_upsert;
    }

    /**
     * @param bool $value
     * @return \Elastica\Document
     */
    public function setDocAsUpsert($value)
    {
        $this->_docAsUpsert = (bool) $value;
    }

    /**
     * @return boolean
     */
    public function getDocAsUpsert()
    {
        return $this->_docAsUpsert;
    }

    /**
     * @param bool $autoPopulate
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
     * @param array $fields if empty array all options will be returned, field names can be either with underscored either without, i.e. _percolate, routing
     * @param bool $withUnderscore should option keys contain underscore prefix
     * @return array
     */
    public function getOptions(array $fields = array(), $withUnderscore = false)
    {
        if (!empty($fields)) {
            $data = array();
            foreach ($fields as $field) {
                $key = '_' . ltrim($field, '_');
                if ($this->hasParam($key) && '' !== (string) $this->getParam($key)) {
                    $data[$key] = $this->getParam($key);
                }
            }
        } else {
            $data = $this->getParams();
        }
        if (!$withUnderscore) {
            foreach ($data as $key => $value) {
                $data[ltrim($key, '_')] = $value;
                unset($data[$key]);
            }
        }
        return $data;
    }

    /**
     * @param  array|\Elastica\Document        $data
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
