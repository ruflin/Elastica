<?php

declare(strict_types=1);

namespace Elastica\Bulk\Action;

use Elastica\AbstractUpdateAction;
use Elastica\Document;

class IndexDocument extends AbstractDocument
{
    /**
     * @var string
     */
    protected $_opType = self::OP_TYPE_INDEX;

    public function setDocument(Document $document): AbstractDocument
    {
        parent::setDocument($document);

        $this->setSource($document->getData());

        return $this;
    }

    protected function _getMetadata(AbstractUpdateAction $action): array
    {
        return $action->getOptions([
            '_index',
            '_id',
            'version',
            'version_type',
            'routing',
            'parent',
            'retry_on_conflict',
        ]);
    }
}
