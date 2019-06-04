<?php

namespace Elastica\Bulk;

use Elastica\Bulk;
use Elastica\Index;
use Elastica\JSON;
use Elastica\Type;

class Action
{
    const OP_TYPE_CREATE = 'create';
    const OP_TYPE_INDEX = 'index';
    const OP_TYPE_DELETE = 'delete';
    const OP_TYPE_UPDATE = 'update';

    /**
     * @var array
     */
    public static $opTypes = [
        self::OP_TYPE_CREATE,
        self::OP_TYPE_INDEX,
        self::OP_TYPE_DELETE,
        self::OP_TYPE_UPDATE,
    ];

    /**
     * @var string
     */
    protected $_opType;

    /**
     * @var array
     */
    protected $_metadata = [];

    /**
     * @var array
     */
    protected $_source = [];

    /**
     * @param string $opType
     * @param array  $metadata
     * @param array  $source
     */
    public function __construct(string $opType = self::OP_TYPE_INDEX, array $metadata = [], array $source = [])
    {
        $this->setOpType($opType);
        $this->setMetadata($metadata);
        $this->setSource($source);
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    public function setOpType(string $type): self
    {
        $this->_opType = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getOpType(): string
    {
        return $this->_opType;
    }

    /**
     * @param array $metadata
     *
     * @return $this
     */
    public function setMetadata(array $metadata): self
    {
        $this->_metadata = $metadata;

        return $this;
    }

    /**
     * @return array
     */
    public function getMetadata(): array
    {
        return $this->_metadata;
    }

    /**
     * @return array
     */
    public function getActionMetadata(): array
    {
        return [$this->_opType => $this->getMetadata()];
    }

    /**
     * @param array|string $source
     *
     * @return $this
     */
    public function setSource($source): self
    {
        $this->_source = $source;

        return $this;
    }

    /**
     * @return array|string
     */
    public function getSource()
    {
        return $this->_source;
    }

    /**
     * @return bool
     */
    public function hasSource(): bool
    {
        return !empty($this->_source);
    }

    /**
     * @param string|Index $index
     *
     * @return $this
     */
    public function setIndex($index): self
    {
        if ($index instanceof Index) {
            $index = $index->getName();
        }
        $this->_metadata['_index'] = $index;

        return $this;
    }

    /**
     * @param string|Type $type
     *
     * @return $this
     */
    public function setType($type): self
    {
        if ($type instanceof Type) {
            $this->setIndex($type->getIndex()->getName());
            $type = $type->getName();
        }
        $this->_metadata['_type'] = $type;

        return $this;
    }

    /**
     * @param string|int $id
     *
     * @return $this
     */
    public function setId($id): self
    {
        $this->_metadata['_id'] = $id;

        return $this;
    }

    /**
     * @param string|int $routing
     *
     * @return $this
     */
    public function setRouting($routing): self
    {
        $this->_metadata['routing'] = $routing;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $data[] = $this->getActionMetadata();
        if ($this->hasSource()) {
            $data[] = $this->getSource();
        }

        return $data;
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        $string = JSON::stringify($this->getActionMetadata(), JSON_FORCE_OBJECT).Bulk::DELIMITER;
        if ($this->hasSource()) {
            $source = $this->getSource();
            if (\is_string($source)) {
                $string .= $source;
            } elseif (\is_array($source) && \array_key_exists('doc', $source) && \is_string($source['doc'])) {
                if (isset($source['doc_as_upsert'])) {
                    $docAsUpsert = ', "doc_as_upsert": '.($source['doc_as_upsert'] ? 'true' : 'false');
                } else {
                    $docAsUpsert = '';
                }
                $string .= '{"doc": '.$source['doc'].$docAsUpsert.'}';
            } else {
                $string .= JSON::stringify($source, JSON_UNESCAPED_UNICODE);
            }
            $string .= Bulk::DELIMITER;
        }

        return $string;
    }

    /**
     * @param string|null $opType
     *
     * @return bool
     */
    public static function isValidOpType(string $opType = null): bool
    {
        return \in_array($opType, self::$opTypes, true);
    }
}
