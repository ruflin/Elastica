<?php

namespace Elastica\Bulk;

use Elastica\Document;
use Elastica\Response as BaseResponse;
use Elastica\Bulk\Action\AbstractDocument as AbstractDocumentAction;

class Response extends BaseResponse
{
    /**
     * @var Action
     */
    protected $_action;

    /**
     * @var string
     */
    protected $_opType;

    /**
     * @param array|string $responseData
     * @param Action $action
     * @param string $opType
     */
    public function __construct($responseData, Action $action, $opType)
    {
        parent::__construct($responseData);

        $this->_action = $action;
        $this->_opType = $opType;

        $this->_init();
    }

    /**
     *
     */
    protected function _init()
    {
        $action = $this->getAction();

        if ($action instanceof AbstractDocumentAction) {
            $document = $action->getDocument();
            if ($document->isAutoPopulate()) {
                $data = $this->getData();
                if (!$document->hasId() && Document::OP_TYPE_CREATE == $this->getOpType() && isset($data['_id'])) {
                    $document->setId($data['_id']);
                }
                if (isset($data['_version'])) {
                    $document->setVersion($data['_version']);
                }
            }
        }
    }

    /**
     * @return Action
     */
    public function getAction()
    {
        return $this->_action;
    }

    /**
     * @return string
     */
    public function getOpType()
    {
        return $this->_opType;
    }
}
