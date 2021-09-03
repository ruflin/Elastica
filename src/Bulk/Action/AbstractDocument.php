<?php

namespace Elastica\Bulk\Action;

use Elastica\AbstractUpdateAction;
use Elastica\Bulk\Action;
use Elastica\Document;
use Elastica\Script\AbstractScript;

abstract class AbstractDocument extends Action
{
    /**
     * @var AbstractScript|Document
     */
    protected $_data;

    /**
     * @param AbstractScript|Document $document
     */
    public function __construct($document)
    {
        $this->setData($document);
    }

    /**
     * @return $this
     */
    public function setDocument(Document $document): self
    {
        $this->_data = $document;

        $metadata = $this->_getMetadata($document);

        $this->setMetadata($metadata);

        return $this;
    }

    /**
     * @return $this
     */
    public function setScript(AbstractScript $script): self
    {
        if (!$this instanceof UpdateDocument) {
            throw new \BadMethodCallException('setScript() can only be used for UpdateDocument');
        }

        $this->_data = $script;

        $metadata = $this->_getMetadata($script);
        $this->setMetadata($metadata);

        return $this;
    }

    /**
     * @param AbstractScript|Document $data
     *
     * @throws \InvalidArgumentException
     *
     * @return $this
     */
    public function setData($data): self
    {
        if ($data instanceof AbstractScript) {
            $this->setScript($data);
        } elseif ($data instanceof Document) {
            $this->setDocument($data);
        } else {
            throw new \InvalidArgumentException('Data should be a Document or a Script.');
        }

        return $this;
    }

    /**
     * Note: This is for backwards compatibility.
     *
     * @return Document|null
     */
    public function getDocument()
    {
        if (!$this->_data instanceof Document) {
            return null;
        }

        return $this->_data;
    }

    /**
     * Note: This is for backwards compatibility.
     *
     * @return AbstractScript|null
     */
    public function getScript()
    {
        if (!$this->_data instanceof AbstractScript) {
            return null;
        }

        return $this->_data;
    }

    /**
     * @return AbstractScript|Document
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     * Creates a bulk action for a document or a script.
     *
     * The action can be index, update, create or delete based on the $opType param (by default index).
     *
     * @param AbstractScript|Document $data
     *
     * @return AbstractDocument
     */
    public static function create($data, ?string $opType = null): self
    {
        //Check type
        if (!$data instanceof Document && !$data instanceof AbstractScript) {
            throw new \InvalidArgumentException('The data needs to be a Document or a Script.');
        }

        if (null === $opType && $data->hasOpType()) {
            $opType = $data->getOpType();
        }

        //Check that scripts can only be used for updates
        if ($data instanceof AbstractScript) {
            if (null === $opType) {
                $opType = self::OP_TYPE_UPDATE;
            } elseif (self::OP_TYPE_UPDATE !== $opType) {
                throw new \InvalidArgumentException('Scripts can only be used with the update operation type.');
            }
        }

        switch ($opType) {
            case self::OP_TYPE_DELETE:
                return new DeleteDocument($data);

            case self::OP_TYPE_CREATE:
                return new CreateDocument($data);

            case self::OP_TYPE_UPDATE:
                return new UpdateDocument($data);

            case self::OP_TYPE_INDEX:
            default:
                return new IndexDocument($data);
        }
    }

    abstract protected function _getMetadata(AbstractUpdateAction $source): array;
}
