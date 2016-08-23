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
    public function __construct($opType = self::OP_TYPE_INDEX, array $metadata = [], array $source = [])
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
    public function setOpType($type)
    {
        $this->_opType = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getOpType()
    {
        return $this->_opType;
    }

    /**
     * @param array $metadata
     *
     * @return $this
     */
    public function setMetadata(array $metadata)
    {
        $this->_metadata = $metadata;

        return $this;
    }

    /**
     * @return array
     */
    public function getMetadata()
    {
        return $this->_metadata;
    }

    /**
     * @return array
     */
    public function getActionMetadata()
    {
        return [$this->_opType => $this->getMetadata()];
    }

    /**
     * @param array $source
     *
     * @return $this
     */
    public function setSource($source)
    {
        $this->_source = $source;

        return $this;
    }

    /**
     * @return array
     */
    public function getSource()
    {
        return $this->_source;
    }

    /**
     * @return bool
     */
    public function hasSource()
    {
        return !empty($this->_source);
    }

    /**
     * @param string|\Elastica\Index $index
     *
     * @return $this
     */
    public function setIndex($index)
    {
        if ($index instanceof Index) {
            $index = $index->getName();
        }
        $this->_metadata['_index'] = $index;

        return $this;
    }

    /**
     * @param string|\Elastica\Type $type
     *
     * @return $this
     */
    public function setType($type)
    {
        if ($type instanceof Type) {
            $this->setIndex($type->getIndex()->getName());
            $type = $type->getName();
        }
        $this->_metadata['_type'] = $type;

        return $this;
    }

    /**
     * @param string $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->_metadata['_id'] = $id;

        return $this;
    }

    /**
     * @param string $routing
     *
     * @return $this
     */
    public function setRouting($routing)
    {
        $this->_metadata['_routing'] = $routing;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
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
    public function toString()
    {
        $string = JSON::stringify($this->getActionMetadata(), JSON_FORCE_OBJECT).Bulk::DELIMITER;
        if ($this->hasSource()) {
            $source = $this->getSource();
            if (is_string($source)) {
                $string .= $source;
            } elseif (is_array($source) && array_key_exists('doc', $source) && is_string($source['doc'])) {
                $docAsUpsert = (isset($source['doc_as_upsert'])) ? ', "doc_as_upsert": '.$source['doc_as_upsert'] : '';
                $string .= '{"doc": '.$source['doc'].$docAsUpsert.'}';
            } else {
                $string .= JSON::stringify($source, JSON_UNESCAPED_UNICODE);
            }
            $string .= Bulk::DELIMITER;
        }

        return $string;
    }

    /**
     * @param string $opType
     *
     * @return bool
     */
    public static function isValidOpType($opType)
    {
        return in_array($opType, self::$opTypes);
    }
}
