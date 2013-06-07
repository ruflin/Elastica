<?php

namespace Elastica\Bulk\Action;

use Elastica\Document;

/**
 * @package Elastica\Bulk\Action
 * @link http://www.elasticsearch.org/guide/reference/api/bulk/
 */
class UpdateDocument extends IndexDocument
{
    /**
     * @var string
     */
    protected $_opType = self::OP_TYPE_UPDATE;

    /**
     * Set the document for this bulk update action.
     * If the given Document object has a script, the script will be used in the update operation.
     * @param \Elastica\Document $document
     * @return \Elastica\Bulk\Action\IndexDocument
     */
    public function setDocument(Document $document)
    {
        parent::setDocument($document);
        if ($document->hasScript()) {
            $source = $document->getScript()->toArray();
            $documentData = $document->getData();
            if (!empty($documentData)) {
                $source['upsert'] = $documentData;
            }
            $this->setSource($source);
        } else {
            $this->setSource(array('doc' => $document->getData()));
        }
        return $this;
    }
}
