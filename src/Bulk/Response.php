<?php

declare(strict_types=1);

namespace Elastica\Bulk;

use Elastica\Response as BaseResponse;

class Response extends BaseResponse
{
    protected Action $_action;

    protected string $_opType;

    /**
     * @param array|string $responseData
     */
    public function __construct($responseData, Action $action, string $opType)
    {
        parent::__construct($responseData);

        $this->_action = $action;
        $this->_opType = $opType;
    }

    public function getAction(): Action
    {
        return $this->_action;
    }

    public function getOpType(): string
    {
        return $this->_opType;
    }
}
