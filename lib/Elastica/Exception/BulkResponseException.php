<?php

namespace Elastica\Exception;

use Elastica\Bulk\ResponseSet;

/**
 * Bulk Response exception
 *
 * @category Xodoa
 * @package Elastica
 */
class BulkResponseException extends AbstractException
{
    /**
     * Response
     *
     * @var \Elastica\Bulk\ResponseSet ResponseSet object
     */
    protected $_responseSet;

    /**
     * Construct Exception
     *
     * @param \Elastica\Bulk\ResponseSet $responseSet
     */
    public function __construct(ResponseSet $responseSet)
    {
        $this->_responseSet = $responseSet;
        parent::__construct('Error in one or more bulk request actions');
    }

    /**
     * Returns bulk response set object
     *
     * @return \Elastica\Bulk\ResponseSet
     */
    public function getResponseSet()
    {
        return $this->_responseSet;
    }

    /**
     * Returns array of failed actions
     *
     * @return array Array of failed actions
     */
    public function getFailures()
    {
        $errors = array();
        foreach ($this->getResponseSet()->getBulkResponses() as $bulkResponse) {
            if ($bulkResponse->hasError()) {
                $error = array(
                    'action' => $bulkResponse->getOpType(),
                ) + $bulkResponse->getData();
                $errors[] = $error;
            }
        }

        return $errors;
    }
}
