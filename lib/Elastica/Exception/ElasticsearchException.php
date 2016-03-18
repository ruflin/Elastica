<?php

namespace Elastica\Exception;

/**
 * Elasticsearch exception.
 *
 * @author Ian Babrou <ibobrik@gmail.com>
 */
class ElasticsearchException extends \Exception implements ExceptionInterface
{
    const REMOTE_TRANSPORT_EXCEPTION = 'RemoteTransportException';

    /**
     * @var string|null Elasticsearch exception name
     */
    private $_exception;

    /**
     * @var bool Whether exception was local to server node or remote
     */
    private $_isRemote = false;

    /**
     * @var array Error array
     */
    protected $_error = array();

    /**
     * Constructs elasticsearch exception.
     *
     * @param int   $code  Error code
     * @param array $error Error object from elasticsearch
     */
    public function __construct($code, $error)
    {
        $this->_error = $error;
        // TODO: es2 improve as now an array
        $this->_parseError(json_encode($error));
        parent::__construct(json_encode($error), $code);
    }

    /**
     * Parse error message from elasticsearch.
     *
     * @param string $error Error message
     */
    protected function _parseError($error)
    {
        $errors = explode(']; nested: ', $error);

        if (count($errors) == 1) {
            $this->_exception = $this->_extractException($errors[0]);
        } else {
            if ($this->_extractException($errors[0]) == self::REMOTE_TRANSPORT_EXCEPTION) {
                $this->_isRemote = true;
                $this->_exception = $this->_extractException($errors[1]);
            } else {
                $this->_exception = $this->_extractException($errors[0]);
            }
        }
    }

    /**
     * Extract exception name from error response.
     *
     * @param string $error
     *
     * @return null|string
     */
    protected function _extractException($error)
    {
        if (preg_match('/^(\w+)\[.*\]/', $error, $matches)) {
            return $matches[1];
        } else {
            return;
        }
    }

    /**
     * Returns elasticsearch exception name.
     *
     * @return string|null
     */
    public function getExceptionName()
    {
        return $this->_exception;
    }

    /**
     * Returns whether exception was local to server node or remote.
     *
     * @return bool
     */
    public function isRemoteTransportException()
    {
        return $this->_isRemote;
    }

    /**
     * @return array Error array
     */
    public function getError()
    {
        return $this->_error;
    }
}
