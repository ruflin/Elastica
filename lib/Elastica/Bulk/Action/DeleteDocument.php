<?php

namespace Elastica\Bulk\Action;

use Elastica\Document;

class DeleteDocument extends AbstractDocument
{
    /**
     * @var string
     */
    protected $_opType = Document::OP_TYPE_DELETE;

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
            '_parent'
        );
        $metadata = $document->getOptions($params, false);

        return $metadata;
    }
}