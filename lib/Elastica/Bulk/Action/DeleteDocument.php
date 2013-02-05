<?php

namespace Elastica\Bulk\Action;

use Elastica\Document;

class DeleteDocument extends AbstractDocument
{
    /**
     * @var string
     */
    protected $_opType = self::OP_TYPE_DELETE;

    /**
     * @param \Elastica\Document $document
     * @return array
     */
    protected function _getMetadataByDocument(Document $document)
    {
        $params = array(
            'index',
            'type',
            'id',
            'version',
            'version_type',
            'routing',
            'parent'
        );
        $metadata = $document->getOptions($params, true);

        return $metadata;
    }
}
