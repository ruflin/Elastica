<?php

declare(strict_types=1);

namespace Elastica\Exception\Bulk\Response;

use Elastica\Bulk\Action;
use Elastica\Bulk\Response;
use Elastica\Exception\BulkException;

class ActionException extends BulkException
{
    protected Response $_response;

    public function __construct(Response $response)
    {
        $this->_response = $response;

        parent::__construct($this->getErrorMessage($response));
    }

    public function getAction(): Action
    {
        return $this->getResponse()->getAction();
    }

    public function getResponse(): Response
    {
        return $this->_response;
    }

    public function getErrorMessage(Response $response): string
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
