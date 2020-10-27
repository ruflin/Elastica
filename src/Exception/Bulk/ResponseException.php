<?php

namespace Elastica\Exception\Bulk;

use Elastica\Bulk\ResponseSet;
use Elastica\Exception\Bulk\Response\ActionException;
use Elastica\Exception\BulkException;

/**
 * Bulk Response exception.
 */
class ResponseException extends BulkException
{
    /**
     * @var ResponseSet ResponseSet object
     */
    protected $_responseSet;

    /**
     * @var ActionException[]
     */
    protected $_actionExceptions = [];

    /**
     * Construct Exception.
     */
    public function __construct(ResponseSet $responseSet)
    {
        $this->_init($responseSet);

        $message = 'Error in one or more bulk request actions:'.\PHP_EOL.\PHP_EOL;
        $message .= $this->getActionExceptionsAsString();

        parent::__construct($message);
    }

    /**
     * Returns bulk response set object.
     */
    public function getResponseSet(): ResponseSet
    {
        return $this->_responseSet;
    }

    /**
     * Returns array of failed actions.
     *
     * @return string[] Array of failed actions
     */
    public function getFailures(): array
    {
        $errors = [];

        foreach ($this->getActionExceptions() as $actionException) {
            $errors[] = $actionException->getMessage();
        }

        return $errors;
    }

    /**
     * @return ActionException[]
     */
    public function getActionExceptions(): array
    {
        return $this->_actionExceptions;
    }

    public function getActionExceptionsAsString(): string
    {
        $message = '';
        foreach ($this->getActionExceptions() as $actionException) {
            $message .= $actionException->getMessage().\PHP_EOL;
        }

        return $message;
    }

    protected function _init(ResponseSet $responseSet): void
    {
        $this->_responseSet = $responseSet;

        foreach ($responseSet->getBulkResponses() as $bulkResponse) {
            if ($bulkResponse->hasError()) {
                $this->_actionExceptions[] = new ActionException($bulkResponse);
            }
        }
    }
}
