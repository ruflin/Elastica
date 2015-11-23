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
    public function setDocument(Document $document)
    {
        parent::setDocument($document);

        $data = $document->getData();

        if (is_array($data)) {
            unset($data['_id']);
        }

        $this->setSource($data);

        return $this;
    }

    /**
     * @param \Elastica\AbstractUpdateAction $action
     *
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
