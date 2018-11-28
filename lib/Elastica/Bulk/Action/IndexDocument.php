<?php
namespace Elastica\Bulk\Action;

use Elastica\AbstractUpdateAction;
use Elastica\Document;

class IndexDocument extends AbstractDocument
{
    /**
     * @var string
     */
    protected $_opType = self::OP_TYPE_INDEX;

    /**
     * @param \Elastica\Document $document
     *
     * @return $this
     */
    public function setDocument(Document $document): AbstractDocument
    {
        parent::setDocument($document);

        $this->setSource($document->getData());

        return $this;
    }

    /**
     * @param \Elastica\AbstractUpdateAction $action
     *
     * @return array
     */
    protected function _getMetadata(AbstractUpdateAction $action): array
    {
        $params = [
            'index',
            'type',
            'id',
            'version',
            'version_type',
            'routing',
            'parent',
            'retry_on_conflict',
        ];

        $metadata = $action->getOptions($params, true);

        return $metadata;
    }
}
