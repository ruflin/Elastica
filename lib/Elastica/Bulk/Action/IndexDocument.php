<?php

namespace Elastica\Bulk\Action;

use Elastica\Bulk\Action;
use Elastica\Document;

class IndexDocument extends AbstractDocument
{
    /**
     * @var string
     */
    protected $_opType = self::OP_TYPE_INDEX;

    /**
     * @param \Elastica\Document $document
     * @return \Elastica\Bulk\Action\IndexDocument
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
            'index',
            'type',
            'id',
            'version',
            'version_type',
            'routing',
            'percolate',
            'parent',
            'ttl',
            'timestamp',
        );
        $metadata = $document->getOptions($params, true);

        return $metadata;
    }
}
