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
        foreach ($this->getResponseSet()->getBulkResponses() as $response) {
            if ($response->hasError()) {
                $error = array(
                    'action' => $response->getAction()->getOpType(),
                );
                $errors[] = $error;
            }
        }

        /*
        foreach ($data['items'] as $item) {
            $meta = reset($item);
            $action = key($item);
            if (isset($meta['error'])) {
                $error = array(
                    'action' => $action,
                );
                foreach ($meta as $key => $value) {
                    $key = ltrim($key, '_');
                    $error[$key] = $value;
                }

                $errors[] = $error;
            }
        }
        */

        return $errors;
    }
}
