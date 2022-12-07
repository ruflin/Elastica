<?php

namespace Elastica\Bulk;

use Elastica\Bulk;
use Elastica\Index;

class Action
{
    public const OP_TYPE_CREATE = 'create';
    public const OP_TYPE_INDEX = 'index';
    public const OP_TYPE_DELETE = 'delete';
    public const OP_TYPE_UPDATE = 'update';

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
     * @var array|string
     */
    protected $_source = [];

    public function __construct(string $opType = self::OP_TYPE_INDEX, array $metadata = [], array $source = [])
    {
        $this->setOpType($opType);
        $this->setMetadata($metadata);
        $this->setSource($source);
    }

    public function __toString(): string
    {
        $string = \json_encode($this->getActionMetadata(), \JSON_FORCE_OBJECT | \JSON_PRESERVE_ZERO_FRACTION | \JSON_THROW_ON_ERROR).Bulk::DELIMITER;

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
                $string .= \json_encode($source, \JSON_UNESCAPED_UNICODE | \JSON_PRESERVE_ZERO_FRACTION | \JSON_THROW_ON_ERROR);
            }
            $string .= Bulk::DELIMITER;
        }

        return $string;
    }

    /**
     * @return $this
     */
    public function setOpType(string $type): self
    {
        $this->_opType = $type;

        return $this;
    }

    public function getOpType(): string
    {
        return $this->_opType;
    }

    /**
     * @return $this
     */
    public function setMetadata(array $metadata): self
    {
        $this->_metadata = $metadata;

        return $this;
    }

    public function getMetadata(): array
    {
        return $this->_metadata;
    }

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

    public function hasSource(): bool
    {
        return !empty($this->_source);
    }

    /**
     * @param Index|string $index
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
     * @return $this
     */
    public function setId(string $id): self
    {
        $this->_metadata['_id'] = $id;

        return $this;
    }

    /**
     * @param int|string $routing
     *
     * @return $this
     */
    public function setRouting($routing): self
    {
        $this->_metadata['routing'] = $routing;

        return $this;
    }

    public function toArray(): array
    {
        $data[] = $this->getActionMetadata();
        if ($this->hasSource()) {
            $data[] = $this->getSource();
        }

        return $data;
    }

    /**
     * @deprecated since version 7.1.3, use the "__toString()" method or cast to string instead.
     */
    public function toString(): string
    {
        \trigger_deprecation('ruflin/elastica', '7.1.3', 'The "%s()" method is deprecated, use "__toString()" or cast to string instead. It will be removed in 8.0.', __METHOD__);

        return (string) $this;
    }

    public static function isValidOpType(?string $opType = null): bool
    {
        return \in_array($opType, self::$opTypes, true);
    }
}
