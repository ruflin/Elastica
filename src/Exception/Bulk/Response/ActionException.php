<?php

namespace Elastica\Exception\Bulk\Response;

use Elastica\Bulk\Action;
use Elastica\Bulk\Response as BulkResponse;
use Elastica\Exception\BulkException;
use Elastica\Response;

class ActionException extends BulkException
{
    /**
     * @var Response
     */
    protected $_response;

    public function __construct(BulkResponse $response)
    {
        $this->_response = $response;

        parent::__construct($this->getErrorMessage($response));
    }

    public function getAction(): Action
    {
        return $this->getResponse()->getAction();
    }

    public function getResponse(): BulkResponse
    {
        return $this->_response;
    }

    public function getErrorMessage(BulkResponse $response): string
    {
        $error = $response->getError();
        $opType = $response->getOpType();
        $data = $response->getData();

        $path = '';
        if (isset($data['_index'])) {
            $path .= '/'.$data['_index'];
        }

        if (isset($data['_type'])) {
            $path .= '/'.$data['_type'];
        }

        if (isset($data['_id'])) {
            $path .= '/'.$data['_id'];
        }

        return "{$opType}: {$path} caused {$error}";
    }
}
