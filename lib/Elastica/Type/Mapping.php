<?php

namespace Elastica\Type;

use Elastica\Exception\InvalidException;
use Elastica\Request;
use Elastica\Type;

/**
 * Elastica Mapping object
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @link http://www.elasticsearch.org/guide/reference/mapping/
 */
class Mapping
{
    /**
     * Mapping
     *
     * @var array Mapping
     */
    protected $_mapping = array();

    /**
     * Type
     *
     * @var \Elastica\Type Type object
     */
    protected $_type = null;

    /**
     * Construct Mapping
     *
     * @param \Elastica\Type $type       OPTIONAL Type object
     * @param array         $properties OPTIONAL Properties
     */
    public function __construct(Type $type = null, array $properties = array())
    {
        if ($type) {
            $this->setType($type);
        }

        if (!empty($properties)) {
            $this->setProperties($properties);
        }
    }

    /**
     * Sets the mapping type
     * Enter description here ...
     * @param  \Elastica\Type             $type Type object
     * @return \Elastica\Type\Mapping Current object
     */
    public function setType(Type $type)
    {
        $this->_type = $type;

        return $this;
    }

    /**
     * Sets the mapping properties
     *
     * @param  array                     $properties Properties
     * @return \Elastica\Type\Mapping Mapping object
     */
    public function setProperties(array $properties)
    {
        return $this->setParam('properties', $properties);
    }

    /**
     * Sets the mapping _meta
     * @param array $meta metadata
     * @return \Elastica\Type\Mapping Mapping object
     * @link http://www.elasticsearch.org/guide/reference/mapping/meta.html
     */
    public function setMeta(array $meta)
    {
        return $this->setParam('_meta', $meta);
    }

    /**
     * Returns mapping type
     *
     * @return \Elastica\Type Type
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Sets source values
     *
     * To disable source, argument is
     * array('enabled' => false)
     *
     * @param  array                     $source Source array
     * @return \Elastica\Type\Mapping Current object
     * @link http://www.elasticsearch.org/guide/reference/mapping/source-field.html
     */
    public function setSource(array $source)
    {
        return $this->setParam('_source', $source);
    }

    /**
     * Disables the source in the index
     *
     * Param can be set to true to enable again
     *
     * @param  bool                      $enabled OPTIONAL (default = false)
     * @return \Elastica\Type\Mapping Current object
     */
    public function disableSource($enabled = false)
    {
        return $this->setSource(array('enabled' => $enabled));
    }

    /**
     * Sets raw parameters
     *
     * Possible options:
     * _uid
     * _id
     * _type
     * _source
     * _all
     * _analyzer
     * _boost
     * _parent
     * _routing
     * _index
     * _size
     * properties
     *
     * @param  string                    $key   Key name
     * @param  mixed                     $value Key value
     * @return \Elastica\Type\Mapping Current object
     */
    public function setParam($key, $value)
    {
        $this->_mapping[$key] = $value;

        return $this;
    }

    /**
     * Sets params for the "_all" field
     *
     * @param array                       $params _all Params (enabled, store, term_vector, analyzer)
     * @return \Elastica\Type\Mapping
     */
    public function setAllField(array $params)
    {
        return $this->setParam('_all', $params);
    }

    /**
     * Enables the "_all" field
     *
     * @param  bool                      $enabled OPTIONAL (default = true)
     * @return \Elastica\Type\Mapping
     */
    public function enableAllField($enabled = true)
    {
        return $this->setAllField(array('enabled' => $enabled));
    }

    /**
     * Set TTL
     *
     * @param  array                     $params TTL Params (enabled, default, ...)
     * @return \Elastica\Type\Mapping
     */
    public function setTtl(array $params)
    {
        return $this->setParam('_ttl', $params);

    }

    /**
     * Enables TTL for all documents in this type
     *
     * @param  bool                      $enabled OPTIONAL (default = true)
     * @return \Elastica\Type\Mapping
     */
    public function enableTtl($enabled = true)
    {
        return $this->setTTL(array('enabled' => $enabled));
    }

    /**
     * Set parent type
     *
     * @param string                     $type Parent type
     * @return \Elastica\Type\Mapping
     */
    public function setParent($type)
    {
        return $this->setParam('_parent', array('type' => $type));
    }

    /**
     * Converts the mapping to an array
     *
     * @throws \Elastica\Exception\InvalidException
     * @return array                               Mapping as array
     */
    public function toArray()
    {
        $type = $this->getType();

        if (empty($type)) {
            throw new InvalidException('Type has to be set');
        }

        return array($type->getName() => $this->_mapping);
    }

    /**
     * Submits the mapping and sends it to the server
     *
     * @return \Elastica\Response Response object
     */
    public function send()
    {
        $path = '_mapping';

        return $this->getType()->request($path, Request::PUT, $this->toArray());
    }

    /**
     * Creates a mapping object
     *
     * @param  array|\Elastica\Type\Mapping     $mapping Mapping object or properties array
     * @return \Elastica\Type\Mapping           Mapping object
     * @throws \Elastica\Exception\InvalidException If invalid type
     */
    public static function create($mapping)
    {
        if (is_array($mapping)) {
            $mappingObject = new Mapping();
            $mappingObject->setProperties($mapping);
        } else {
            $mappingObject = $mapping;
        }

        if (!$mappingObject instanceof Mapping) {
            throw new InvalidException('Invalid object type');
        }

        return $mappingObject;
    }
}
