<?php

declare(strict_types=1);

namespace Elastica\Bulk\Action;

use Elastica\AbstractUpdateAction;

class DeleteDocument extends AbstractDocument
{
    /**
     * @var string
     */
    protected $_opType = self::OP_TYPE_DELETE;

    protected function _getMetadata(AbstractUpdateAction $action): array
    {
        $metadata = $action->getOptions([
            '_index',
            '_id',
            'version_type',
            'routing',
            'parent',
        ]);

        return $this->_addConcurrencyControlMetadata($action, $metadata);
    }
}
