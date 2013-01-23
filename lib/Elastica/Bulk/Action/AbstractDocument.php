<?php

namespace Elastica\Bulk\Action;

use Elastica\Bulk\Action;
use Elastica\Document;

abstract class AbstractDocument extends Action
{
    /**
     * @var Document
     */
    protected $_document;

    /**
     * @param Document $document
     */
    public function __construct(Document $document)
    {
        $this->setDocument($document);
    }

    /**
     * @param \Elastica\Document $document
     * @return \Elastica\Bulk\AbstractDocument
     */
    public function setDocument(Document $document)
    {
        $this->_document = $document;

        $metadata = $this->_getMetadataByDocument($document);;

        $this->setMetadata($metadata);

        return $this;
    }

    /**
     * @return \Elastica\Document
     */
    public function getDocument()
    {
        return $this->_document;
    }

    /**
     * @param \Elastica\Document $document
     * @return array
     */
    abstract protected function _getMetadataByDocument(Document $document);

    /**
     * @param \Elastica\Document $document
     * @param string $opType
     * @return AbstractDocument
     */
    public static function create(Document $document, $opType = null)
    {
        if (null === $opType && $document->hasOpType()) {
            $opType = $document->getOpType();
        }

        switch ($opType) {
            case Document::OP_TYPE_DELETE:
                $action = new DeleteDocument($document);
                break;
            case Document::OP_TYPE_CREATE:
                $action = new CreateDocument($document);
                break;
            case Document::OP_TYPE_INDEX:
            default:
                $action = new IndexDocument($document);
                break;
        }
        return $action;
    }
}