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
     * @param \Elastica\Document $document
     * @return \Elastica\Bulk\Action\UpdateDocument
     */
    public function setDocument(Document $document)
    {
        parent::setDocument($document);
        
        $source = array('doc' => $document->getData());
        
        if ($document->hasUpsert()) {
            $upsert = $document->getUpsert()->getData();
            
            if (!empty($upsert)) {
                $source['upsert'] = $upsert;
            }
        }
        
        $this->setSource($source);
        
        return $this;
    }
}
