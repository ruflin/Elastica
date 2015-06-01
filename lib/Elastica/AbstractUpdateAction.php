<?php
namespace Elastica;

/**
 * Base class for things that can be sent to the update api (Document and
 * Script).
 *
 * @author   Nik Everett <nik9000@gmail.com>
 */
class AbstractUpdateAction extends Param
{
    /**
     * @var \Elastica\Document
     */
    protected $_upsert;

    /**
     * Sets the id of the document.
     *
     * @param string $id
     *
     * @return $this
     */
    public function setId($id)
    {
        return $this->setParam('_id', $id);
    }

    /**
     * Returns document id.
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
     * Sets lifetime of document.
     *
     * @param string $ttl
     *
     * @return $this
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
     * Sets the document type name.
     *
     * @param string $type Type name
     *
     * @return $this
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
     * Return document type name.
     *
     * @throws \Elastica\Exception\InvalidException
     *
     * @return string Document type name
     */
    public function getType()
    {
        return $this->getParam('_type');
    }

    /**
     * Sets the document index name.
     *
     * @param string $index Index name
     *
     * @return $this
     */
    public function setIndex($index)
    {
        if ($index instanceof Index) {
            $index = $index->getName();
        }

        return $this->setParam('_index', $index);
    }

    /**
     * Get the document index name.
     *
     * @throws \Elastica\Exception\InvalidException
     *
     * @return string Index name
     */
    public function getIndex()
    {
        return $this->getParam('_index');
    }

    /**
     * Sets the version of a document for use with optimistic concurrency control.
     *
     * @param int $version Document version
     *
     * @return $this
     *
     * @link https://www.elastic.co/blog/versioning
     */
    public function setVersion($version)
    {
        return $this->setParam('_version', (int) $version);
    }

    /**
     * Returns document version.
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
     * Default in ES is internal, but you can set to external to use custom versioning.
     *
     * @param int $versionType Document version type
     *
     * @return $this
     */
    public function setVersionType($versionType)
    {
        return $this->setParam('_version_type', $versionType);
    }

    /**
     * Returns document version type.
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
     * Sets parent document id.
     *
     * @param string|int $parent Parent document id
     *
     * @return $this
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/mapping-parent-field.html
     */
    public function setParent($parent)
    {
        return $this->setParam('_parent', $parent);
    }

    /**
     * Returns the parent document id.
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
     * Set operation type.
     *
     * @param string $opType Only accept create
     *
     * @return $this
     */
    public function setOpType($opType)
    {
        return $this->setParam('_op_type', $opType);
    }

    /**
     * Get operation type.
     *
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
     * Set percolate query param.
     *
     * @param string $value percolator filter
     *
     * @return $this
     */
    public function setPercolate($value = '*')
    {
        return $this->setParam('_percolate', $value);
    }

    /**
     * Get percolate parameter.
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
     * Set routing query param.
     *
     * @param string $value routing
     *
     * @return $this
     */
    public function setRouting($value)
    {
        return $this->setParam('_routing', $value);
    }

    /**
     * Get routing parameter.
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
     *
     * @return $this
     */
    public function setFields($fields)
    {
        if (is_array($fields)) {
            $fields = implode(',', $fields);
        }

        return $this->setParam('_fields', (string) $fields);
    }

    /**
     * @return $this
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
     *
     * @return $this
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
     *
     * @return $this
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
     *
     * @return $this
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
     *
     * @return $this
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
     *
     * @return $this
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
     *
     * @return $this
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
     * @param \Elastica\Document|array $data
     *
     * @return $this
     */
    public function setUpsert($data)
    {
        $document = Document::create($data);
        $this->_upsert = $document;

        return $this;
    }

    /**
     * @return \Elastica\Document
     */
    public function getUpsert()
    {
        return $this->_upsert;
    }

    /**
     * @return bool
     */
    public function hasUpsert()
    {
        return null !== $this->_upsert;
    }

    /**
     * @param array $fields         if empty array all options will be returned, field names can be either with underscored either without, i.e. _percolate, routing
     * @param bool  $withUnderscore should option keys contain underscore prefix
     *
     * @return array
     */
    public function getOptions(array $fields = array(), $withUnderscore = false)
    {
        if (!empty($fields)) {
            $data = array();
            foreach ($fields as $field) {
                $key = '_'.ltrim($field, '_');
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
}
