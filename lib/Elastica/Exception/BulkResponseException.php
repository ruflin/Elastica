<?php

namespace Elastica\Exception;

use Elastica\Response;

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
     * @var \Elastica\Response Response object
     */
    protected $_response = null;

    /**
     * Construct Exception
     *
     * @param \Elastica\Response $response
     */
    public function __construct(Response $response)
    {
        $this->_response = $response;
        parent::__construct('Error in one or more bulk request actions');
    }

    /**
     * Returns response object
     *
     * @return \Elastica\Response Response object
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * Returns array of failed actions
     *
     * @return array Array of failed actions
     */
    public function getFailures()
    {
        $data = $this->_response->getData();
        $errors = array();

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

        return $errors;
    }
}
