<?php

namespace Elastica;

use Elastica\Bulk\Action;
use Elastica\Exception\InvalidException;

/**
 * Single document stored in elastic search.
 *
 * @author   Nicolas Ruflin <spam@ruflin.com>
 */
class Document extends AbstractUpdateAction
{
    public const OP_TYPE_CREATE = Action::OP_TYPE_CREATE;

    /**
     * Document data.
     *
     * @var array Document data
     */
    protected $_data = [];

    /**
     * Whether to use this document to upsert if the document does not exist.
     *
     * @var bool
     */
    protected $_docAsUpsert = false;

    /**
     * @var bool
     */
    protected $_autoPopulate = false;

    /**
     * Creates a new document.
     *
     * @param string|null  $id    The document ID, if null it will be created
     * @param array|string $data  Data array
     * @param Index|string $index Index name
     */
    public function __construct(?string $id = null, $data = [], $index = '')
    {
        $this->setId($id);
        $this->setData($data);
        $this->setIndex($index);
    }

    /**
     * @return mixed
     */
    public function __get(string $key)
    {
        return $this->get($key);
    }

    /**
     * @param mixed $value
     */
    public function __set(string $key, $value): void
    {
        $this->set($key, $value);
    }

    public function __isset(string $key): bool
    {
        return $this->has($key) && null !== $this->get($key);
    }

    public function __unset(string $key): void
    {
        $this->remove($key);
    }

    /**
     * Get the value of the given field.
     *
     * @param mixed $key
     *
     * @throws InvalidException If the given field does not exist
     *
     * @return mixed
     */
    public function get($key)
    {
        if (!$this->has($key)) {
            throw new InvalidException("Field {$key} does not exist");
        }

        return $this->_data[$key];
    }

    /**
     * Set the value of the given field.
     *
     * @param mixed $value
     *
     * @throws InvalidException if the current document is a serialized data
     */
    public function set(string $key, $value): self
    {
        if (!\is_array($this->_data)) {
            throw new InvalidException('Document data is serialized data. Data creation is forbidden.');
        }
        $this->_data[$key] = $value;

        return $this;
    }

    /**
     * Returns if the current document has the given field.
     */
    public function has(string $key): bool
    {
        return \is_array($this->_data) && \array_key_exists($key, $this->_data);
    }

    /**
     * Removes a field from the document, by the given key.
     *
     * @throws InvalidException if the given field does not exist
     */
    public function remove(string $key): self
    {
        if (!$this->has($key)) {
            throw new InvalidException("Field {$key} does not exist");
        }
        unset($this->_data[$key]);

        return $this;
    }

    /**
     * Adds a file to the index.
     *
     * To use this feature you have to call the following command in the
     * elasticsearch directory:
     * <code>
     * ./bin/plugin -install elasticsearch/elasticsearch-mapper-attachments/1.6.0
     * </code>
     * This installs the tika file analysis plugin. More infos about supported formats
     * can be found here: {@link http://tika.apache.org/0.7/formats.html}
     *
     * @param string $key      Key to add the file to
     * @param string $filepath Path to add the file
     * @param string $mimeType Header mime type
     */
    public function addFile(string $key, string $filepath, string $mimeType = ''): self
    {
        $value = \base64_encode(\file_get_contents($filepath));

        if (!empty($mimeType)) {
            $value = ['_content_type' => $mimeType, '_name' => $filepath, '_content' => $value];
        }

        $this->set($key, $value);

        return $this;
    }

    /**
     * Add file content.
     */
    public function addFileContent(string $key, string $content): self
    {
        return $this->set($key, \base64_encode($content));
    }

    /**
     * Adds a geopoint field to the document.
     *
     * @param string $key Field key
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/geo-point.html
     */
    public function addGeoPoint(string $key, float $latitude, float $longitude): self
    {
        $value = ['lat' => $latitude, 'lon' => $longitude];

        $this->set($key, $value);

        return $this;
    }

    /**
     * Overwrites the current document data with the given data.
     *
     * @param array|string $data Data array
     */
    public function setData($data): self
    {
        $this->_data = $data;

        return $this;
    }

    /**
     * Returns the document data.
     *
     * @return array|string Document data
     */
    public function getData()
    {
        return $this->_data;
    }

    public function setDocAsUpsert(bool $value): self
    {
        $this->_docAsUpsert = $value;

        return $this;
    }

    public function getDocAsUpsert(): bool
    {
        return $this->_docAsUpsert;
    }

    public function setAutoPopulate(bool $autoPopulate = true): self
    {
        $this->_autoPopulate = $autoPopulate;

        return $this;
    }

    public function isAutoPopulate(): bool
    {
        return $this->_autoPopulate;
    }

    public function setPipeline(string $pipeline): self
    {
        return $this->setParam('pipeline', $pipeline);
    }

    public function getPipeline(): string
    {
        return $this->getParam('pipeline');
    }

    public function hasPipeline(): bool
    {
        return $this->hasParam('pipeline');
    }

    /**
     * Returns the document as an array.
     */
    public function toArray(): array
    {
        $doc = $this->getParams();
        $doc['_source'] = $this->getData();

        return $doc;
    }

    /**
     * @param array|Document $data
     *
     * @throws InvalidException If invalid data has been provided
     */
    public static function create($data): self
    {
        if ($data instanceof self) {
            return $data;
        }

        if (\is_array($data)) {
            return new static('', $data);
        }

        throw new InvalidException('Failed to create document. Invalid data passed.');
    }
}
