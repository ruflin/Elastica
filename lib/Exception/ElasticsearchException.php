<?php

namespace Elastica\Exception;

/**
 * Elasticsearch exception
 *
 * @category Xodoa
 * @package Elastica
 * @author Ian Babrou <ibobrik@gmail.com>
 */
class ElasticsearchException extends \Exception
{

    const REMOTE_TRANSPORT_EXCEPTION = 'RemoteTransportException';

    /**
     * Elasticsearch exception name
     *
     * @var string|null
     */
    private $_exception;

    /**
     * Whether exception was local to server node or remote
     *
     * @var bool
     */
    private $_isRemote = false;

    /**
     * Constructs elasticsearch exception
     *
     * @param int $code Error code
     * @param string $error Error message from elasticsearch
     */
    public function __construct($code, $error)
    {
        $this->_parseError($error);
        parent::__construct($error, $code);
    }

    /**
     * Parse error message from elasticsearch
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
     * Extract exception name from error response
     *
     * @param string $error
     * @return null|string
     */
    protected function _extractException($error)
    {
        if (preg_match('/^(\w+)\[.*\]/', $error, $matches)) {
            return $matches[1];
        } else {
            return null;
        }
    }

    /**
     * Returns elasticsearch exception name
     *
     * @return string|null
     */
    public function getExceptionName()
    {
        return $this->_exception;
    }

    /**
     * Returns whether exception was local to server node or remote
     *
     * @return bool
     */
    public function isRemoteTransportException()
    {
        return $this->_isRemote;
    }

}
