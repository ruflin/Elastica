<?php

namespace Elastica\Bulk;

class Action
{
    /**
     * @var string
     */
    protected $_opType;

    /**
     * @var array
     */
    protected $_metadata = array();

    /**
     * @var array
     */
    protected $_source = array();

    /**
     * @param string $opType
     * @param array $metadata
     * @param array $source
     */
    public function __construct($opType, array $metadata = array(), array $source = array())
    {
        $this->setOpType($opType);
        $this->setMetadata($metadata);
        $this->setSource($source);
    }

    /**
     * @param string $type
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
        return array($this->_opType => $this->getMetadata());
    }

    /**
     * @param array $source
     * @return $this
     */
    public function setSource(array $source)
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
}