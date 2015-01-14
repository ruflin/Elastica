<?php

namespace Elastica\Bulk\Action;

use Elastica\AbstractUpdateAction;
use Elastica\Bulk\Action;
use Elastica\Document;

class IndexDocument extends AbstractDocument
{
    /**
     * @var string
     */
    protected $_opType = self::OP_TYPE_INDEX;

    /**
     * @param  \Elastica\Document                  $document
     * @return \Elastica\Bulk\Action\IndexDocument
     */
    public function setDocument(Document $document)
    {
        parent::setDocument($document);

        $this->setSource($document->getData());

        return $this;
    }

    /**
     * @param  \Elastica\AbstractUpdateAction $source
     * @return array
     */
    protected function _getMetadata(AbstractUpdateAction $action)
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
            'retry_on_conflict',
        );
        $metadata = $action->getOptions($params, true);

        return $metadata;
    }
}
