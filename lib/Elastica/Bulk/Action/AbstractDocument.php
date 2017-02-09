<?php
namespace Elastica\Bulk\Action;

use Elastica\AbstractUpdateAction;
use Elastica\Bulk\Action;
use Elastica\Document;
use Elastica\Script\AbstractScript;

abstract class AbstractDocument extends Action
{
    /**
     * @var Document|AbstractScript
     */
    protected $_data;

    /**
     * @param Document|AbstractScript $document
     */
    public function __construct($document)
    {
        $this->setData($document);
    }

    /**
     * @param Document $document
     *
     * @return $this
     */
    public function setDocument(Document $document)
    {
        $this->_data = $document;

        $metadata = $this->_getMetadata($document);

        $this->setMetadata($metadata);

        return $this;
    }

    /**
     * @param AbstractScript $script
     *
     * @return $this
     */
    public function setScript(AbstractScript $script)
    {
        if (!($this instanceof UpdateDocument)) {
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
    public function setData($data)
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
        if ($this->_data instanceof Document) {
            return $this->_data;
        }

        return;
    }

    /**
     * Note: This is for backwards compatibility.
     *
     * @return AbstractScript|null
     */
    public function getScript()
    {
        if ($this->_data instanceof AbstractScript) {
            return $this->_data;
        }

        return;
    }

    /**
     * @return Document|AbstractScript
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     * @param \Elastica\AbstractUpdateAction $source
     *
     * @return array
     */
    abstract protected function _getMetadata(AbstractUpdateAction $source);

    /**
     * Creates a bulk action for a document or a script.
     *
     * The action can be index, update, create or delete based on the $opType param (by default index).
     *
     * @param Document|AbstractScript $data
     * @param string                  $opType
     *
     * @return static
     */
    public static function create($data, $opType = null)
    {
        //Check type
        if (!($data instanceof Document) && !($data instanceof AbstractScript)) {
            throw new \InvalidArgumentException('The data needs to be a Document or a Script.');
        }

        if (null === $opType && $data->hasOpType()) {
            $opType = $data->getOpType();
        }

        //Check that scripts can only be used for updates
        if ($data instanceof AbstractScript) {
            if ($opType === null) {
                $opType = self::OP_TYPE_UPDATE;
            } elseif ($opType != self::OP_TYPE_UPDATE) {
                throw new \InvalidArgumentException('Scripts can only be used with the update operation type.');
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
