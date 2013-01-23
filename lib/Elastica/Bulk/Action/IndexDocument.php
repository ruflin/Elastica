<?php

namespace Elastica\Bulk\Action;

use Elastica\Document;

class IndexDocument extends AbstractDocument
{
    /**
     * @var string
     */
    protected $_opType = Document::OP_TYPE_INDEX;

    /**
     * @param \Elastica\Document $document
     * @return $this
     */
    public function setDocument(Document $document)
    {
        parent::setDocument($document);

        $this->setSource($document->getData());

        return $this;
    }

    /**
     * @param \Elastica\Document $document
     * @return array
     */
    protected function _getMetadataByDocument(Document $document)
    {
        $params = array(
            '_index',
            '_type',
            '_id',
            '_version',
            '_version_type',
            '_routing',
            '_percolate',
            '_parent',
            '_ttl',
            '_timestamp',
        );
        $metadata = $document->getOptions($params, false);

        return $metadata;
    }

}