<?php

namespace Elastica\Bulk\Action;

use Elastica\AbstractUpdateAction;
use Elastica\Bulk\Action;
use Elastica\Document;
use Elastica\Script;

abstract class AbstractDocument extends Action
{
    /**
     * @var \Elastica\Document|\Elastica\Script
     */
    protected $_data;

    /**
     * @param \Elastica\Document|\Elastica\Script $document
     */
    public function __construct($document)
    {
        $this->setData($document);
    }

    /**
     * @param  \Elastica\Document                     $document
     * @return \Elastica\Bulk\Action\AbstractDocument
     */
    public function setDocument(Document $document)
    {
        $this->_data = $document;

        $metadata = $this->_getMetadata($document);

        $this->setMetadata($metadata);

        return $this;
    }

    /**
     * @param  \Elastica\Script                       $script
     * @return \Elastica\Bulk\Action\AbstractDocument
     */
    public function setScript(Script $script)
    {
        if (!($this instanceof UpdateDocument)) {
            throw new \BadMethodCallException("setScript() can only be used for UpdateDocument");
        }

        $this->_data = $script;

        $metadata = $this->_getMetadata($script);
        $this->setMetadata($metadata);

        return $this;
    }

    /**
     * @param  \Elastica\Script|\Elastica\Document    $data
     * @throws \InvalidArgumentException
     * @return \Elastica\Bulk\Action\AbstractDocument
     */
    public function setData($data)
    {
        if ($data instanceof Script) {
            $this->setScript($data);
        } elseif ($data instanceof Document) {
            $this->setDocument($data);
        } else {
            throw new \InvalidArgumentException("Data should be a Document or a Script.");
        }

        return $this;
    }

    /**
     * Note: This is for backwards compatibility.
     * @return \Elastica\Document
     */
    public function getDocument()
    {
        if ($this->_data instanceof Document) {
            return $this->_data;
        }

        return;
    }

    /**
     * Note: This is for backwards compatibility.
     * @return \Elastica\Script
     */
    public function getScript()
    {
        if ($this->_data instanceof Script) {
            return $this->_data;
        }

        return;
    }

    /**
     * @return \Elastica\Document|\Elastica\Script
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     * @param  \Elastica\AbstractUpdateAction $source
     * @return array
     */
    abstract protected function _getMetadata(AbstractUpdateAction $source);

    /**
     * @param  \Elastica\Document|\Elastica\Script    $data
     * @param  string                                 $opType
     * @return \Elastica\Bulk\Action\AbstractDocument
     */
    public static function create($data, $opType = null)
    {
        //Check type
        if (!($data instanceof Document) && !($data instanceof Script)) {
            throw new \InvalidArgumentException("The data needs to be a Document or a Script.");
        }

        if (null === $opType && $data->hasOpType()) {
            $opType = $data->getOpType();
        }

        //Check that scripts can only be used for updates
        if ($data instanceof Script) {
            if ($opType === null) {
                $opType = self::OP_TYPE_UPDATE;
            } elseif ($opType != self::OP_TYPE_UPDATE) {
                throw new \InvalidArgumentException("Scripts can only be used with the update operation type.");
            }
        }

        switch ($opType) {
            case self::OP_TYPE_DELETE:
                $action = new DeleteDocument($data);
                break;
            case self::OP_TYPE_CREATE:
                $action = new CreateDocument($data);
                break;
            case self::OP_TYPE_UPDATE:
                $action = new UpdateDocument($data);
                break;
            case self::OP_TYPE_INDEX:
            default:
                $action = new IndexDocument($data);
                break;
        }

        return $action;
    }
}
