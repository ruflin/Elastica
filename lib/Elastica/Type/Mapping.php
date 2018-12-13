<?php
namespace Elastica\Type;

use Elastica\Exception\InvalidException;
use Elastica\Response;
use Elastica\Type;
use Elasticsearch\Endpoints\Indices\Mapping\Put;

/**
 * Elastica Mapping object.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/mapping.html
 */
class Mapping
{
    /**
     * Mapping.
     *
     * @var array Mapping
     */
    protected $_mapping = [];

    /**
     * Type.
     *
     * @var \Elastica\Type Type object
     */
    protected $_type;

    /**
     * Construct Mapping.
     *
     * @param Type  $type       OPTIONAL Type object
     * @param array $properties OPTIONAL Properties
     */
    public function __construct(Type $type = null, array $properties = [])
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
     *
     * @param Type $type Type object
     *
     * @return $this
     */
    public function setType(Type $type): Mapping
    {
        $this->_type = $type;

        return $this;
    }

    /**
     * Sets the mapping properties.
     *
     * @param array $properties Properties
     *
     * @return $this
     */
    public function setProperties(array $properties): Mapping
    {
        return $this->setParam('properties', $properties);
    }

    /**
     * Gets the mapping properties.
     *
     * @return array $properties Properties
     */
    public function getProperties(): array
    {
        return $this->getParam('properties');
    }

    /**
     * Sets the mapping _meta.
     *
     * @param array $meta metadata
     *
     * @return $this
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/mapping-meta.html
     */
    public function setMeta(array $meta): Mapping
    {
        return $this->setParam('_meta', $meta);
    }

    /**
     * Returns mapping type.
     *
     * @return Type Type
     */
    public function getType(): Type
    {
        return $this->_type;
    }

    /**
     * Sets source values.
     *
     * To disable source, argument is
     * array('enabled' => false)
     *
     * @param array $source Source array
     *
     * @return $this
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/mapping-source-field.html
     */
    public function setSource(array $source): Mapping
    {
        return $this->setParam('_source', $source);
    }

    /**
     * Disables the source in the index.
     *
     * Param can be set to true to enable again
     *
     * @param bool $enabled OPTIONAL (default = false)
     *
     * @return $this
     */
    public function disableSource(bool $enabled = false): Mapping
    {
        return $this->setSource(['enabled' => $enabled]);
    }

    /**
     * Sets raw parameters.
     *
     * Possible options:
     * _uid
     * _id
     * _type
     * _source
     * _analyzer
     * _boost
     * _routing
     * _index
     * _size
     * properties
     *
     * @param string $key   Key name
     * @param mixed  $value Key value
     *
     * @return $this
     */
    public function setParam(string $key, $value): Mapping
    {
        $this->_mapping[$key] = $value;

        return $this;
    }

    /**
     * Get raw parameters.
     *
     * @see setParam
     *
     * @param string $key Key name
     *
     * @return mixed $value Key value
     */
    public function getParam(string $key)
    {
        return $this->_mapping[$key] ?? null;
    }

    /**
     * Converts the mapping to an array.
     *
     * @throws InvalidException
     *
     * @return array Mapping as array
     */
    public function toArray(): array
    {
        $type = $this->getType();

        if (empty($type)) {
            throw new InvalidException('Type has to be set');
        }

        return [$type->getName() => $this->_mapping];
    }

    /**
     * Submits the mapping and sends it to the server.
     *
     * @param array $query Query string parameters to send with mapping
     *
     * @return Response Response object
     */
    public function send(array $query = []): Response
    {
        $endpoint = new Put();
        $endpoint->setBody($this->toArray());
        $endpoint->setParams($query);

        return $this->getType()->requestEndpoint($endpoint);
    }

    /**
     * Creates a mapping object.
     *
     * @param array|Mapping $mapping Mapping object or properties array
     *
     * @throws InvalidException If invalid type
     *
     * @return self
     */
    public static function create($mapping): Mapping
    {
        if (is_array($mapping)) {
            $mappingObject = new self();
            $mappingObject->setProperties($mapping);

            return $mappingObject;
        }

        if ($mapping instanceof self) {
            return $mapping;
        }

        throw new InvalidException('Invalid object type');
    }
}
