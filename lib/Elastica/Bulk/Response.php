<?php

namespace Elastica\Bulk;

use Elastica\Response as BaseResponse;

class Response extends BaseResponse
{
    /**
     * @var Action
     */
    protected $_action;

    /**
     * @param Action $action
     * @return $this
     */
    public function setAction(Action $action)
    {
        $this->_action = $action;

        return $this;
    }

    /**
     * @return Action
     */
    public function getAction()
    {
        return $this->_action;
    }
}