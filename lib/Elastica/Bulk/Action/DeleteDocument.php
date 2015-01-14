<?php

namespace Elastica\Bulk\Action;

use Elastica\AbstractUpdateAction;

class DeleteDocument extends AbstractDocument
{
    /**
     * @var string
     */
    protected $_opType = self::OP_TYPE_DELETE;

    /**
     * @param  \Elastica\AbstractUpdateAction $action
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
            'parent',
        );
        $metadata = $action->getOptions($params, true);

        return $metadata;
    }
}
