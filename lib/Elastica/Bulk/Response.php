<?php

namespace Elastica\Bulk;

use Elastica\Response as BaseResponse;

class Response extends BaseResponse
{
    /**
     * @var \Elastica\Bulk\Action
     */
    protected $_action;

    /**
     * @var string
     */
    protected $_opType;

    /**
     * @param array|string $responseData
     * @param Action       $action
     * @param string       $opType
     */
    public function __construct($responseData, Action $action, string $opType)
    {
        parent::__construct($responseData);

        $this->_action = $action;
        $this->_opType = $opType;
    }

    /**
     * @return Action
     */
    public function getAction(): Action
    {
        return $this->_action;
    }

    /**
     * @return string
     */
    public function getOpType(): string
    {
        return $this->_opType;
    }
}
